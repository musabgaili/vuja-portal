<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\AppleTokenVerifier;
use App\Services\Auth\GoogleTokenVerifier;
use App\Services\Auth\IssuesApiToken;
use App\Services\Auth\SocialTokenAuthService;
use App\Services\Fcm\FcmTokenService;
use App\Support\Auth\SocialIdentity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

/**
 * Mobile API authentication (Laravel Sanctum personal access tokens).
 * One app, role-switched: the /me + login payloads carry the user's type/role
 * booleans so the client can branch its UI.
 *
 * Social login (Google / Apple) is native-SDK first: the app verifies with the
 * provider, then POSTs the ID token here. Browser OAuth with a deep-link
 * callback is available as a fallback for Google.
 */
class AuthController extends Controller
{
    public function __construct(
        private IssuesApiToken $tokens,
        private SocialTokenAuthService $social,
        private GoogleTokenVerifier $google,
        private AppleTokenVerifier $apple,
        private FcmTokenService $fcm,
    ) {}

    /** Exchange email + password for a bearer token. */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => [__('auth.failed')]]);
        }

        $this->assertLoginAllowed($user);

        return response()->json($this->tokens->issue($user, $data['device_name'] ?? null));
    }

    /**
     * Native Google Sign-In: Flutter google_sign_in → idToken → Sanctum token.
     * Does not create staff accounts (invite-only) unless social_auto_register is on.
     */
    public function google(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_token' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $identity = $this->google->verify($data['id_token']);
        $user = $this->social->authenticate($identity);

        return response()->json($this->tokens->issue($user, $data['device_name'] ?? null));
    }

    /**
     * Native Sign in with Apple: Flutter sign_in_with_apple → identityToken.
     */
    public function apple(Request $request): JsonResponse
    {
        $data = $request->validate([
            'identity_token' => 'required|string',
            'nonce' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'device_name' => 'nullable|string|max:255',
        ]);

        $identity = $this->apple->verify($data['identity_token'], $data['nonce'] ?? null);

        if ($identity->name === null && ! empty($data['full_name'])) {
            $identity = new SocialIdentity(
                provider: $identity->provider,
                providerId: $identity->providerId,
                email: $identity->email,
                name: $data['full_name'],
                emailVerified: $identity->emailVerified,
            );
        }

        $user = $this->social->authenticate($identity);

        return response()->json($this->tokens->issue($user, $data['device_name'] ?? null));
    }

    /**
     * Browser-based Google OAuth fallback. Redirects back into the app via
     * `{scheme}://auth/callback?token=...` after a successful login.
     */
    public function googleRedirect(Request $request)
    {
        $state = base64_encode(json_encode([
            'device_name' => (string) $request->query('device_name', 'mobile'),
        ], JSON_THROW_ON_ERROR));

        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirectUrl($this->googleMobileCallbackUrl())
            ->redirect();
    }

    public function googleCallback(Request $request)
    {
        $socialUser = Socialite::driver('google')
            ->stateless()
            ->redirectUrl($this->googleMobileCallbackUrl())
            ->user();

        $verified = filter_var(data_get($socialUser->user ?? [], 'email_verified', true), FILTER_VALIDATE_BOOLEAN);

        $identity = new SocialIdentity(
            provider: 'google',
            providerId: (string) $socialUser->getId(),
            email: $socialUser->getEmail() ? strtolower($socialUser->getEmail()) : null,
            name: $socialUser->getName(),
            emailVerified: $verified,
        );

        $user = $this->social->authenticate($identity);

        $device = 'mobile';
        $state = $request->query('state');
        if (is_string($state) && $state !== '') {
            $decoded = json_decode(base64_decode($state, true) ?: '', true);
            if (is_array($decoded) && ! empty($decoded['device_name'])) {
                $device = (string) $decoded['device_name'];
            }
        }

        $issued = $this->tokens->issue($user, $device);
        $scheme = config('mobile.scheme', 'vujade');

        return redirect($scheme.'://auth/callback?token='.urlencode($issued['token']));
    }

    /** The current authenticated user. */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /** Update name, phone, locale. */
    public function updateMe(Request $request): UserResource
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^[0-9+ ]+$/'],
            'locale' => 'sometimes|required|in:en,ar',
        ]);

        $request->user()->update($data);

        return new UserResource($request->user()->fresh());
    }

    /** Change password (does not revoke other devices unless asked). */
    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
            'revoke_other_devices' => 'sometimes|boolean',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update(['password' => $data['password']]);

        if ($request->boolean('revoke_other_devices')) {
            $current = $user->currentAccessToken();
            $user->tokens()->when(
                $current,
                fn ($q) => $q->where('id', '!=', $current->id),
            )->delete();
        }

        return response()->json(['message' => 'Password updated.']);
    }

    /** Revoke only the token used for this request (this device). */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->input('fcm_token');
        if (is_string($token) && $token !== '') {
            $this->fcm->unregister($request->user(), $token);
        }

        $this->revokeBearerToken($request);

        return response()->json(['message' => 'Logged out.']);
    }

    /** Revoke every Sanctum token and FCM registration for this user. */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->fcm->unregisterAll($request->user());
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out of all devices.']);
    }

    private function assertLoginAllowed(User $user): void
    {
        if (in_array($user->status, [\App\Enums\UserStatus::SUSPENDED, \App\Enums\UserStatus::INACTIVE], true)) {
            throw ValidationException::withMessages(['email' => ['This account is not active.']]);
        }
    }

    private function googleMobileCallbackUrl(): string
    {
        return url('/api/v1/auth/google/callback');
    }

    /**
     * Always revoke the Sanctum token from the Authorization header.
     * currentAccessToken() is a TransientToken when the web guard already has
     * a user (Sanctum checks session first), which would no-op a delete().
     */
    private function revokeBearerToken(Request $request): void
    {
        $plain = $request->bearerToken();
        if (is_string($plain) && $plain !== '') {
            $model = \Laravel\Sanctum\Sanctum::personalAccessTokenModel();
            $model::findToken($plain)?->delete();

            return;
        }

        $current = $request->user()?->currentAccessToken();
        if ($current instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $current->delete();
        }
    }
}
