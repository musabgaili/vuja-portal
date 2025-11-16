<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Services\IdeaRequestController;
use App\Http\Controllers\Services\ConsultationRequestController;
use App\Http\Controllers\Services\ResearchRequestController;
use App\Http\Controllers\Services\IpRegistrationController;
use App\Http\Controllers\Services\CopyrightRegistrationController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
|
| All routes accessible by clients (authenticated users with 'client' role)
|
*/

Route::prefix('client')->middleware(['auth', 'verified'])->group(function () {
    
    // Client Dashboard
    Route::get('/dashboard', [DashboardController::class, 'clientDashboard'])->name('client.dashboard');
    
    // My Requests - Unified view of all requests
    Route::get('/my-requests', [\App\Http\Controllers\ClientRequestsController::class, 'index'])->name('client.requests');
    
    // Projects moved to routes/projects.php
    
    // Meetings - Client Booking
    Route::get('/meetings/book', [\App\Http\Controllers\MeetingController::class, 'availableSlots'])->name('meetings.available-slots');
    Route::get('/meetings/book/{timeSlot}', [\App\Http\Controllers\MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/meetings/book/{timeSlot}', [\App\Http\Controllers\MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/my-meetings', [\App\Http\Controllers\MeetingController::class, 'myMeetings'])->name('meetings.my-meetings');
    Route::delete('/meetings/{meeting}/cancel', [\App\Http\Controllers\MeetingController::class, 'cancel'])->name('meetings.cancel');
    
    // Services Overview
    Route::get('/services', function () {
        return view('services.index');
    })->name('services.index');
    
    // ============================================
    // IDEA GENERATION SERVICE
    // ============================================
    Route::prefix('ideas')->name('ideas.')->group(function () {
        // Client Actions
        Route::get('/create', [IdeaRequestController::class, 'create'])->name('create');
        Route::post('/', [IdeaRequestController::class, 'store'])->name('store');
        Route::get('/{idea}', [IdeaRequestController::class, 'show'])->name('show');
        
        // AI Assessment
        Route::get('/{idea}/ai-assessment', [IdeaRequestController::class, 'showAiAssessment'])->name('ai-assessment');
        Route::post('/{idea}/ai-assessment', [IdeaRequestController::class, 'processAiAssessment'])->name('ai-assessment.process');
        
        // Negotiation
        Route::get('/{idea}/negotiation', [IdeaRequestController::class, 'showNegotiation'])->name('negotiation');
        Route::post('/{idea}/comments', [IdeaRequestController::class, 'addComment'])->name('comments.store');
        
        // Quote Actions
        Route::post('/{idea}/accept-quote', [IdeaRequestController::class, 'acceptQuote'])->name('accept-quote');
        Route::post('/{idea}/reject-quote', [IdeaRequestController::class, 'rejectQuote'])->name('reject-quote');
        
        // Payment
        Route::get('/{idea}/payment', [IdeaRequestController::class, 'showPayment'])->name('payment');
        Route::post('/{idea}/payment', [IdeaRequestController::class, 'uploadPayment'])->name('payment.upload');
    });
    
    // ============================================
    // CONSULTATION SERVICE
    // ============================================
    Route::prefix('consultations')->name('consultations.')->group(function () {
        Route::get('/create', [ConsultationRequestController::class, 'create'])->name('create');
        Route::post('/', [ConsultationRequestController::class, 'store'])->name('store');
        Route::get('/{consultation}', [ConsultationRequestController::class, 'show'])->name('show');
    });
    
    // ============================================
    // RESEARCH & IP SERVICE
    // ============================================
    Route::prefix('research')->name('research.')->group(function () {
        Route::get('/create', [ResearchRequestController::class, 'create'])->name('create');
        Route::post('/', [ResearchRequestController::class, 'store'])->name('store');
        Route::get('/{research}', [ResearchRequestController::class, 'show'])->name('show');
        Route::post('/{research}/sign-documents', [ResearchRequestController::class, 'signDocuments'])->name('sign-documents');
        Route::post('/{research}/book-meeting', [ResearchRequestController::class, 'bookMeeting'])->name('book-meeting');
    });
    
    // ============================================
    // IP REGISTRATION SERVICE
    // ============================================
    Route::prefix('ip')->name('ip.')->group(function () {
        Route::get('/create', [IpRegistrationController::class, 'create'])->name('create');
        Route::post('/', [IpRegistrationController::class, 'store'])->name('store');
        Route::get('/{ip}', [IpRegistrationController::class, 'show'])->name('show');
        Route::post('/{ip}/book-meeting', [IpRegistrationController::class, 'bookMeeting'])->name('book-meeting');
    });
    
    // ============================================
    // COPYRIGHT REGISTRATION SERVICE
    // ============================================
    Route::prefix('copyright')->name('copyright.')->group(function () {
        Route::get('/create', [CopyrightRegistrationController::class, 'create'])->name('create');
        Route::post('/', [CopyrightRegistrationController::class, 'store'])->name('store');
        Route::get('/{copyright}', [CopyrightRegistrationController::class, 'show'])->name('show');
        Route::post('/{copyright}/book-meeting', [CopyrightRegistrationController::class, 'bookMeeting'])->name('book-meeting');
    });
    
});
