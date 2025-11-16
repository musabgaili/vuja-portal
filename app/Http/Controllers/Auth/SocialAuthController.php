<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialAuthController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * Redirect to social provider.
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, $this->socialAuthService->getSupportedProviders())) {
            abort(404);
        }

        return redirect($this->socialAuthService->getRedirectUrl($provider));
    }

    /**
     * Handle social provider callback.
     */
    public function callback(string $provider)
    {
        if (!in_array($provider, $this->socialAuthService->getSupportedProviders())) {
            abort(404);
        }

        try {
            $user = $this->socialAuthService->handleCallback($provider);
            
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Welcome to VujaDe Platform!');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Social login failed. Please try again.');
        }
    }
}
