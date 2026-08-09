<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================
// PUBLIC ROUTES
// ============================================

Route::get('language/{locale}', function (string $locale) {
    if (! in_array($locale, config('app.supported_locales', ['en', 'ar']), true)) {
        return redirect()->back();
    }

    session(['locale' => $locale]);
    app()->setLocale($locale);

    return redirect()->back();
})->name('locale');

Route::get('/', function () {
    // return Auth::user()->isInternal();
    // // $x= User::find(Auth::user()->id)->update(['type' => 'internal']);
    // return $x;
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// ============================================
// AUTHENTICATION ROUTES
// ============================================

Auth::routes(['verify' => true]);

// Referral share link (spec §10): <APP_URL>/r/<code> remembers the code for the
// next registration, then sends the visitor to sign up.
Route::get('/r/{code}', function (string $code) {
    app(\App\Services\Engagement\ReferralService::class)->rememberCode($code);

    return redirect()->route('register');
})->name('referral.capture');

// NOTE: logout is POST-only (Auth::routes registers `POST /logout` named 'logout').
// The nav submits a CSRF-protected POST form; a GET logout route was removed to
// close a logout-CSRF vector (a state change on an unprotected GET).

// Social Authentication Routes
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// Client invitation — a signed link (emailed when staff "invite" a new client)
// lets the client set a password and activate their account. Guest-accessible;
// authorised by the URL signature.
Route::get('/invite/{user}/accept', [\App\Http\Controllers\Auth\InviteController::class, 'show'])
    ->name('invite.accept')->middleware('signed');
Route::post('/invite/{user}/accept', [\App\Http\Controllers\Auth\InviteController::class, 'store'])
    ->middleware('signed');

// Public payment request: the emailed/copied page is signed and expires, while
// billing/attempt posts are protected by the unguessable UUID, CSRF and DB expiry.
Route::get('/pay/{paymentRequest}', [\App\Http\Controllers\PublicPaymentRequestController::class, 'show'])
    ->middleware(['signed', 'throttle:60,1'])
    ->name('payments.public.show');
Route::get('/pay/{paymentRequest}/quote.pdf', \App\Http\Controllers\PublicPaymentQuoteController::class)
    ->middleware(['signed', 'throttle:30,1'])
    ->name('payments.public.quote');
Route::post('/pay/{paymentRequest}/billing', [\App\Http\Controllers\PublicPaymentRequestController::class, 'billing'])
    ->middleware('throttle:20,1')
    ->name('payments.public.billing');
Route::post('/pay/{paymentRequest}/attempts', [\App\Http\Controllers\PublicPaymentAttemptController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('payments.public.attempts.store');
Route::get('/pay/{paymentRequest}/callback', \App\Http\Controllers\MoyasarCallbackController::class)
    ->middleware('throttle:60,1')
    ->name('payments.callback');
Route::post('/webhooks/moyasar', \App\Http\Controllers\MoyasarWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('webhooks.moyasar');

// ============================================
// AUTHENTICATED ROUTES
// ============================================

Route::middleware(['auth'])->group(function () {

    // Main dashboard route - redirects based on role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Home route redirects to main dashboard
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

    // PROFILE MANAGEMENT (All authenticated users)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/email', [\App\Http\Controllers\ProfileController::class, 'updateEmail'])->name('profile.update-email');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::put('/profile/phone', [\App\Http\Controllers\ProfileController::class, 'updatePhone'])->name('profile.update-phone');
    Route::get('/profile/security', [\App\Http\Controllers\ProfileController::class, 'security'])->name('profile.security');
    Route::delete('/profile/delete-account', [\App\Http\Controllers\ProfileController::class, 'deleteAccount'])->name('profile.delete-account');

    // Service Request Routes (Legacy - can be removed later)
    Route::resource('service-requests', ServiceRequestController::class);

    // Service-work deliverable download — reachable by internal staff and by the
    // client owner when the deliverable was shared. Authorization is enforced in
    // the controller (assignee/manager/PM, or client owner for client-visible files).
    Route::get('/service-work/items/{item}/download', [\App\Http\Controllers\Services\ServiceWorkController::class, 'download'])
        ->middleware('throttle:60,1')
        ->name('service-work.download');

    // Internal Improvement Ideas — any authenticated user (client, employee or
    // PM) can suggest portal improvements. Managers review & approve them in the
    // internal area (see routes/internal.php). Views are role-aware.
    Route::prefix('improvement-ideas')->name('improvement-ideas.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Services\ImprovementIdeaController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Services\ImprovementIdeaController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Services\ImprovementIdeaController::class, 'store'])->name('store');
        Route::get('/{improvementIdea}', [\App\Http\Controllers\Services\ImprovementIdeaController::class, 'show'])->name('show');
    });

});

// ============================================
// INCLUDE CLIENT ROUTES
// ============================================
require __DIR__.'/client.php';

// ============================================
// INCLUDE INTERNAL ROUTES (Manager, Employee, PM)
// ============================================
require __DIR__.'/internal.php';

// ============================================
// INCLUDE PROJECT ROUTES (Client + Internal)
// ============================================
require __DIR__.'/projects.php';

// ============================================
// INCLUDE INVENTORY ROUTES (Manager)
// ============================================
require __DIR__.'/inventory.php';
