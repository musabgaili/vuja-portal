<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceRequestController;
use App\Models\CopyrightRegistration;
use App\Models\IpRegistration;
use App\Models\Project;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

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

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// ============================================
// AUTHENTICATION ROUTES
// ============================================

Auth::routes(['verify' => true]);

// Social Authentication Routes
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// ============================================
// AUTHENTICATED ROUTES
// ============================================

Route::middleware(['auth',])->group(function () {
    
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