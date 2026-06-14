<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    protected function redirectTo(): string
    {
        return route('client.dashboard');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        // Rate-limit registration attempts per IP to blunt bot volume.
        $this->middleware('throttle:6,1')->only('register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // ---- Anti-bot (silent; legitimate users never see these) ----
            // Honeypot: a hidden field. Bots fill it; humans leave it empty.
            'website' => ['prohibited'],
            // Signed form token issued when the page rendered. Blind POST bots
            // don't have it, and it traps too-fast / stale submissions.
            'form_token' => ['required', 'string', function ($attribute, $value, $fail) {
                try {
                    $startedAt = (int) decrypt($value);
                } catch (\Throwable $e) {
                    $fail('Invalid submission.');

                    return;
                }
                $elapsed = now()->timestamp - $startedAt;
                if ($elapsed < 3) {
                    $fail('Form submitted too quickly — please try again.');
                } elseif ($elapsed > 7200) {
                    $fail('This form has expired — please reload and try again.');
                }
            }],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::PENDING,
        ]);

        $user->assignRole('client');

        // NOTE: do NOT fire Registered here — the RegistersUsers trait's register()
        // already dispatches it. Firing it twice sent two verification emails and,
        // worse, the trait's (unwrapped) dispatch 500'd on any mail-transport
        // error. Mail resilience now lives in User::sendEmailVerificationNotification().
        return $user;
    }
}
