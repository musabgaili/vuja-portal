<?php

use App\Http\Controllers\ControlTowerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EngagementController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\WeeklyPlannerController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\ServiceRequestTypeController;
use App\Http\Controllers\Services\ConsultationRequestController;
use App\Http\Controllers\Services\CopyrightRegistrationController;
use App\Http\Controllers\Services\IdeaRequestController;
use App\Http\Controllers\Services\IpRegistrationController;
use App\Http\Controllers\Services\ResearchRequestController;
use App\Http\Controllers\StepFormFieldController;
use App\Http\Controllers\StepperServiceRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal Routes (Manager, Employee, Project Manager)
|--------------------------------------------------------------------------
|
| All routes accessible by internal staff (managers, employees, PMs)
|
*/
Route::prefix('internal')->middleware(['auth', 'is_internal'])->group(function () {

    // Internal Dashboard
    Route::get('/', [DashboardController::class, 'internalDashboard'])->name('internal.dashboard');

    // ============================================
    // MANAGER & EMPLOYEE ROUTES (Internal Staff Only)
    // The outer group already enforces auth + is_internal.
    // ============================================

    // ENGAGEMENT — Impact Points (all internal users; managers get the Owner view)
    Route::get('/engagement', [EngagementController::class, 'index'])->name('engagement.index');
    Route::post('/engagement/thank-you', [EngagementController::class, 'thankYou'])->name('engagement.thank-you');

    // ENGAGEMENT POINTS (client loyalty) — discretionary grants: a PM suggests
    // here; only a Manager may approve (gated in the controller). PMs need to see
    // the queue, so it lives outside the manager-only group below.
    Route::get('/engagement-points/grants', [\App\Http\Controllers\Admin\EngagementGrantController::class, 'index'])->name('engagement.admin.grants.index');
    Route::post('/engagement-points/grants', [\App\Http\Controllers\Admin\EngagementGrantController::class, 'store'])->name('engagement.admin.grants.store');
    Route::post('/engagement-points/grants/{grant}/approve', [\App\Http\Controllers\Admin\EngagementGrantController::class, 'approve'])->name('engagement.admin.grants.approve');
    Route::post('/engagement-points/grants/{grant}/reject', [\App\Http\Controllers\Admin\EngagementGrantController::class, 'reject'])->name('engagement.admin.grants.reject');

    // PERFORMANCE TARGETS — my targets + live progress + 6-month trend (all internal staff)
    Route::get('/my-targets', [\App\Http\Controllers\StaffTargetController::class, 'index'])->name('targets.my');

    // CAPACITY — my weekly allocation + monthly capacity (team members / engineers)
    Route::get('/capacity', [\App\Http\Controllers\CapacityController::class, 'index'])->name('capacity.my');
    Route::post('/capacity/week', [\App\Http\Controllers\CapacityController::class, 'save'])->name('capacity.save');

    // NOTIFICATIONS — mark the bell feed as seen (all internal users)
    Route::post('/notifications/seen', [\App\Http\Controllers\NotificationController::class, 'seen'])->name('notifications.seen');

    // TEAM CHAT (internal staff) — channels + DMs, @mentions, threads, reactions.
    // Literal segments are registered BEFORE the {channel} wildcard so they match.
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/mentions', [\App\Http\Controllers\ChatController::class, 'mentions'])->name('chat.mentions');
    Route::post('/chat/mentions/read-all', [\App\Http\Controllers\ChatController::class, 'readAllMentions'])->name('chat.mentions.read-all');
    Route::get('/chat/poll', [\App\Http\Controllers\ChatController::class, 'poll'])->name('chat.poll');
    Route::get('/chat/members', [\App\Http\Controllers\ChatController::class, 'members'])->name('chat.members');
    Route::post('/chat/channels', [\App\Http\Controllers\ChatController::class, 'storeChannel'])->name('chat.channels.store');
    Route::post('/chat/dm', [\App\Http\Controllers\ChatController::class, 'startDm'])->name('chat.dm');
    Route::put('/chat/messages/{message}', [\App\Http\Controllers\ChatController::class, 'updateMessage'])->name('chat.messages.update');
    Route::delete('/chat/messages/{message}', [\App\Http\Controllers\ChatController::class, 'destroyMessage'])->name('chat.messages.destroy');
    Route::post('/chat/messages/{message}/react', [\App\Http\Controllers\ChatController::class, 'react'])->name('chat.messages.react');
    Route::get('/chat/{channel}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/{channel}/messages', [\App\Http\Controllers\ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/{channel}/messages', [\App\Http\Controllers\ChatController::class, 'storeMessage'])->name('chat.messages.store');
    Route::get('/chat/{channel}/thread/{message}', [\App\Http\Controllers\ChatController::class, 'thread'])->name('chat.thread');

    // APPROVAL QUEUE — everything awaiting a manager/PM decision
    Route::get('/approvals', [\App\Http\Controllers\ApprovalController::class, 'index'])->name('approvals.index');

    // DIRECT STAFF TASKS — assignee inbox + status updates (all internal);
    // creation / deletion is manager-only (see the is_manager group below).
    Route::get('/staff-tasks', [\App\Http\Controllers\StaffTaskController::class, 'index'])->name('staff-tasks.index');
    Route::post('/staff-tasks/{staffTask}/status', [\App\Http\Controllers\StaffTaskController::class, 'updateStatus'])->name('staff-tasks.status');

    // CRM — sales pipeline (Leads / Opportunities), all internal staff
    Route::get('/crm', [\App\Http\Controllers\OpportunityController::class, 'index'])->name('crm.index');
    Route::get('/crm/create', [\App\Http\Controllers\OpportunityController::class, 'create'])->name('crm.create');
    Route::post('/crm', [\App\Http\Controllers\OpportunityController::class, 'store'])->name('crm.store');
    Route::get('/crm/{opportunity}', [\App\Http\Controllers\OpportunityController::class, 'show'])->name('crm.show');
    Route::get('/crm/{opportunity}/edit', [\App\Http\Controllers\OpportunityController::class, 'edit'])->name('crm.edit');
    Route::put('/crm/{opportunity}', [\App\Http\Controllers\OpportunityController::class, 'update'])->name('crm.update');
    Route::post('/crm/{opportunity}/stage', [\App\Http\Controllers\OpportunityController::class, 'updateStage'])->name('crm.stage');
    Route::post('/crm/{opportunity}/lost', [\App\Http\Controllers\OpportunityController::class, 'markLost'])->name('crm.lost');
    Route::post('/crm/{opportunity}/convert', [\App\Http\Controllers\OpportunityController::class, 'convert'])->name('crm.convert');
    Route::delete('/crm/{opportunity}', [\App\Http\Controllers\OpportunityController::class, 'destroy'])->name('crm.destroy');

    // CRM address book — companies & contacts
    Route::resource('companies', \App\Http\Controllers\CompanyController::class);
    Route::resource('contacts', \App\Http\Controllers\ContactController::class)->except('show');

    // CRM reporting — sales analytics (manager)
    Route::get('/crm-reports', [\App\Http\Controllers\CrmReportController::class, 'index'])->name('crm-reports.index');

    // CRM activities & chatter (log notes / schedule next-actions; My Activities inbox)
    Route::get('/crm-activities', [\App\Http\Controllers\CrmActivityController::class, 'index'])->name('crm-activities.index');
    Route::post('/crm-activities', [\App\Http\Controllers\CrmActivityController::class, 'store'])->name('crm-activities.store');
    Route::post('/crm-activities/{activity}/complete', [\App\Http\Controllers\CrmActivityController::class, 'complete'])->name('crm-activities.complete');
    Route::delete('/crm-activities/{activity}', [\App\Http\Controllers\CrmActivityController::class, 'destroy'])->name('crm-activities.destroy');

    // AI SCOPE & INVENTORY PLANNER (all internal — front-end of the Pricing Tool)
    Route::get('/scope-planner', [\App\Http\Controllers\ScopePlannerController::class, 'index'])->name('scope-planner.index');
    Route::post('/scope-planner', [\App\Http\Controllers\ScopePlannerController::class, 'plan'])->name('scope-planner.plan');
    Route::post('/scope-planner/save-quote', [\App\Http\Controllers\ScopePlannerController::class, 'saveQuote'])->name('scope-planner.save-quote');

    // Upgraded staged document flow (create -> confirm items -> generate -> preview/edit -> export).
    // NOTE: /create is declared before /{quote} so it is not bound as a Quote.
    Route::get('/scope-planner/create', [\App\Http\Controllers\ScopePlannerController::class, 'create'])->name('scope-planner.create');
    Route::post('/scope-planner/create', [\App\Http\Controllers\ScopePlannerController::class, 'store'])->name('scope-planner.store');
    Route::get('/scope-planner/{quote}', [\App\Http\Controllers\ScopePlannerController::class, 'show'])->name('scope-planner.show');
    Route::post('/scope-planner/{quote}/suggest', [\App\Http\Controllers\ScopePlannerController::class, 'suggest'])->name('scope-planner.suggest');
    Route::put('/scope-planner/{quote}/items', [\App\Http\Controllers\ScopePlannerController::class, 'saveItems'])->name('scope-planner.items');
    Route::post('/scope-planner/{quote}/generate', [\App\Http\Controllers\ScopePlannerController::class, 'generate'])->name('scope-planner.generate');
    Route::put('/scope-planner/{quote}', [\App\Http\Controllers\ScopePlannerController::class, 'update'])->name('scope-planner.update');
    Route::post('/scope-planner/{quote}/regenerate', [\App\Http\Controllers\ScopePlannerController::class, 'regenerateSection'])->name('scope-planner.regenerate');
    Route::post('/scope-planner/{quote}/finalize', [\App\Http\Controllers\ScopePlannerController::class, 'finalize'])->name('scope-planner.finalize');
    Route::post('/scope-planner/{quote}/reopen', [\App\Http\Controllers\ScopePlannerController::class, 'reopen'])->name('scope-planner.reopen');

    // Document render + export.
    Route::get('/scope-planner/{quote}/document', [\App\Http\Controllers\Scope\DocumentController::class, 'document'])->name('scope-planner.document');
    Route::get('/scope-planner/{quote}/export/pdf', [\App\Http\Controllers\Scope\DocumentController::class, 'pdf'])->name('scope-planner.export.pdf');
    Route::get('/scope-planner/{quote}/view/pdf', [\App\Http\Controllers\Scope\DocumentController::class, 'viewPdf'])->name('scope-planner.view.pdf');
    Route::get('/scope-planner/{quote}/export/docx', [\App\Http\Controllers\Scope\DocumentController::class, 'docx'])->name('scope-planner.export.docx');
    Route::get('/scope-planner/{quote}/technical/pdf', [\App\Http\Controllers\Scope\DocumentController::class, 'technicalPdf'])->name('scope-planner.technical.pdf');

    // QUOTES (internal) — built from the Scope Planner; accepted -> order/project
    Route::get('/quotes', [\App\Http\Controllers\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/{quote}', [\App\Http\Controllers\QuoteController::class, 'show'])->name('quotes.show');
    Route::post('/quotes/{quote}/send', [\App\Http\Controllers\QuoteController::class, 'send'])->name('quotes.send');
    Route::post('/quotes/{quote}/assign-client', [\App\Http\Controllers\QuoteController::class, 'assignClient'])->name('quotes.assign-client');
    Route::post('/quotes/{quote}/accept', [\App\Http\Controllers\QuoteController::class, 'acceptInternal'])->name('quotes.accept-internal');
    // Internal approval layer
    Route::post('/quotes/{quote}/submit', [\App\Http\Controllers\QuoteController::class, 'submitForApproval'])->name('quotes.submit');
    Route::post('/quotes/{quote}/approve', [\App\Http\Controllers\QuoteController::class, 'approve'])->name('quotes.approve');
    Route::post('/quotes/{quote}/reject', [\App\Http\Controllers\QuoteController::class, 'reject'])->name('quotes.reject');
    Route::post('/quotes/{quote}/request-changes', [\App\Http\Controllers\QuoteController::class, 'requestChanges'])->name('quotes.request-changes');
    Route::post('/quotes/{quote}/comments', [\App\Http\Controllers\QuoteController::class, 'addComment'])->name('quotes.comments.store');
    Route::delete('/quotes/{quote}', [\App\Http\Controllers\QuoteController::class, 'destroy'])->name('quotes.destroy');

    // Spend & reimbursement requests (employee/PM/manager) — project + general,
    // reimbursement + purchase, role-routed approvals.
    Route::get('/spend', [\App\Http\Controllers\SpendRequestController::class, 'index'])->name('spend.index');
    Route::get('/spend/create', [\App\Http\Controllers\SpendRequestController::class, 'create'])->name('spend.create');
    Route::post('/spend', [\App\Http\Controllers\SpendRequestController::class, 'store'])->name('spend.store');
    Route::get('/spend/approvals', [\App\Http\Controllers\SpendRequestController::class, 'approvals'])->name('spend.approvals');
    Route::post('/spend/{spendRequest}/approve', [\App\Http\Controllers\SpendRequestController::class, 'approve'])->name('spend.approve');
    Route::post('/spend/{spendRequest}/reject', [\App\Http\Controllers\SpendRequestController::class, 'reject'])->name('spend.reject');
    Route::post('/spend/{spendRequest}/purchase', [\App\Http\Controllers\SpendRequestController::class, 'purchase'])->name('spend.purchase');
    Route::post('/spend/{spendRequest}/reimburse', [\App\Http\Controllers\SpendRequestController::class, 'reimburse'])->name('spend.reimburse');
    Route::get('/spend/{spendRequest}/receipt/{which?}', [\App\Http\Controllers\SpendRequestController::class, 'receipt'])->name('spend.receipt');
    Route::delete('/spend/{spendRequest}', [\App\Http\Controllers\SpendRequestController::class, 'destroy'])->name('spend.destroy');

    // Customer Invoices (PM + manager create/manage; client uploads receipts elsewhere)
    Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/mark-paid', [\App\Http\Controllers\InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::post('/invoices/{invoice}/reopen', [\App\Http\Controllers\InvoiceController::class, 'reopen'])->name('invoices.reopen');
    Route::post('/invoices/{invoice}/cancel', [\App\Http\Controllers\InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::get('/invoices/{invoice}/file', [\App\Http\Controllers\InvoiceController::class, 'downloadFile'])->name('invoices.file');
    Route::get('/invoices/{invoice}/receipt', [\App\Http\Controllers\InvoiceController::class, 'downloadReceipt'])->name('invoices.receipt');

    // CONTROL TOWER — owner project-health traffic light + risk alerts (manager)
    Route::get('/control-tower', [ControlTowerController::class, 'index'])->name('control-tower.index');

    // WORKLOAD HEATMAP — who is busy / who is idle (manager)
    Route::get('/workload', [\App\Http\Controllers\WorkloadController::class, 'index'])->name('workload.index');
    Route::get('/workload/{user}', [\App\Http\Controllers\WorkloadController::class, 'show'])->name('workload.show');

    // WEEKLY STRATEGIC PLANNER (employees plan; managers review/oversee)
    Route::get('/weekly-planner', [WeeklyPlannerController::class, 'index'])->name('weekly-planner.index');
    Route::post('/weekly-planner', [WeeklyPlannerController::class, 'store'])->name('weekly-planner.store');
    Route::get('/weekly-planner/review', [WeeklyPlannerController::class, 'review'])->name('weekly-planner.review');
    Route::get('/weekly-planner/presence', [WeeklyPlannerController::class, 'presence'])->name('weekly-planner.presence');
    Route::get('/weekly-planner/plan/{weeklyPlan}', [WeeklyPlannerController::class, 'show'])->name('weekly-planner.show');
    Route::post('/weekly-planner/{weeklyPlan}/approve', [WeeklyPlannerController::class, 'approve'])->name('weekly-planner.approve');
    Route::post('/weekly-planner/{weeklyPlan}/reject', [WeeklyPlannerController::class, 'reject'])->name('weekly-planner.reject');

    // TIME SLOTS - My Availability (All internal users)
    Route::get('/my-time-slots', [\App\Http\Controllers\TimeSlotController::class, 'mySlots'])->name('time-slots.my-slots');
    Route::get('/time-slots/create', [\App\Http\Controllers\TimeSlotController::class, 'create'])->name('time-slots.create');
    Route::post('/time-slots', [\App\Http\Controllers\TimeSlotController::class, 'store'])->name('time-slots.store');
    Route::delete('/time-slots/{timeSlot}', [\App\Http\Controllers\TimeSlotController::class, 'destroy'])->name('time-slots.destroy');
    Route::post('/time-slots/{timeSlot}/toggle-block', [\App\Http\Controllers\TimeSlotController::class, 'toggleBlock'])->name('time-slots.toggle-block');
    Route::get('/time-slots/available/{employeeId}', [\App\Http\Controllers\TimeSlotController::class, 'getAvailableSlots'])->name('time-slots.available');

    // MEETINGS
    Route::get('/my-meetings', [\App\Http\Controllers\MeetingController::class, 'myMeetings'])->name('meetings.internal.my-meetings');
    Route::post('/meetings/{meeting}/confirm', [\App\Http\Controllers\MeetingController::class, 'confirm'])->name('meetings.confirm');
    Route::post('/meetings/{meeting}/complete', [\App\Http\Controllers\MeetingController::class, 'complete'])->name('meetings.complete');

    // Projects moved to routes/projects.php

    // IDEAS - Manager/Employee Routes
    Route::get('/ideas/manager', [IdeaRequestController::class, 'managerIndex'])->name('ideas.manager.index');
    Route::get('/ideas/manager/{idea}', [IdeaRequestController::class, 'managerShow'])->name('ideas.manager.show');
    Route::post('/ideas/{idea}/send-quote', [IdeaRequestController::class, 'sendQuote'])->name('ideas.send-quote');
    Route::post('/ideas/{idea}/approve-quote', [IdeaRequestController::class, 'approveQuote'])->name('ideas.approve-quote');
    Route::post('/ideas/{idea}/verify-payment', [IdeaRequestController::class, 'verifyPayment'])->name('ideas.verify-payment');
    Route::post('/ideas/{idea}/assign', [IdeaRequestController::class, 'assign'])->name('ideas.assign');
    Route::post('/ideas/{idea}/close', [IdeaRequestController::class, 'close'])->name('ideas.close');
    Route::post('/ideas/{idea}/convert-to-project', [IdeaRequestController::class, 'convertToProject'])->name('ideas.convert-to-project');

    // PROTOTYPES - Manager/Employee Routes
    Route::get('/prototypes/manager', [\App\Http\Controllers\Services\PrototypeRequestController::class, 'managerIndex'])->name('prototypes.manager.index');
    Route::get('/prototypes/manager/{prototype}', [\App\Http\Controllers\Services\PrototypeRequestController::class, 'managerShow'])->name('prototypes.manager.show');
    Route::get('/prototypes/files/{file}/download', [\App\Http\Controllers\Services\PrototypeRequestController::class, 'downloadFile'])->name('prototypes.manager.files.download');
    Route::post('/prototypes/{prototype}/assign', [\App\Http\Controllers\Services\PrototypeRequestController::class, 'assign'])->name('prototypes.assign');
    Route::post('/prototypes/{prototype}/status', [\App\Http\Controllers\Services\PrototypeRequestController::class, 'updateStatus'])->name('prototypes.update-status');
    Route::post('/prototypes/{prototype}/convert-to-project', [\App\Http\Controllers\Services\PrototypeRequestController::class, 'convertToProject'])->name('prototypes.convert-to-project');

    // 3D LAB - Manager/Employee Routes (printing + design)
    Route::get('/3d/manager', [\App\Http\Controllers\Services\ThreeDRequestController::class, 'managerIndex'])->name('threed.manager.index');
    Route::get('/3d/manager/{threed}', [\App\Http\Controllers\Services\ThreeDRequestController::class, 'managerShow'])->name('threed.manager.show');
    Route::get('/3d/files/{file}/download', [\App\Http\Controllers\Services\ThreeDRequestController::class, 'downloadFile'])->name('threed.manager.files.download');
    Route::post('/3d/{threed}/assign', [\App\Http\Controllers\Services\ThreeDRequestController::class, 'assign'])->name('threed.assign');
    Route::post('/3d/{threed}/status', [\App\Http\Controllers\Services\ThreeDRequestController::class, 'updateStatus'])->name('threed.update-status');
    Route::post('/3d/{threed}/convert-to-project', [\App\Http\Controllers\Services\ThreeDRequestController::class, 'convertToProject'])->name('threed.convert-to-project');

    // Improvement Ideas — manager review & approval (approval awards Impact Points)
    Route::get('/improvement-ideas/manager', [\App\Http\Controllers\Services\ImprovementIdeaController::class, 'managerIndex'])->name('improvement-ideas.manager.index');
    Route::get('/improvement-ideas/manager/{improvementIdea}', [\App\Http\Controllers\Services\ImprovementIdeaController::class, 'managerShow'])->name('improvement-ideas.manager.show');
    Route::post('/improvement-ideas/{improvementIdea}/approve', [\App\Http\Controllers\Services\ImprovementIdeaController::class, 'approve'])->name('improvement-ideas.approve');
    Route::post('/improvement-ideas/{improvementIdea}/reject', [\App\Http\Controllers\Services\ImprovementIdeaController::class, 'reject'])->name('improvement-ideas.reject');

    // CONSULTATIONS - Manager/Employee Routes
    Route::get('/consultations/manager', [ConsultationRequestController::class, 'managerIndex'])->name('consultations.manager.index');
    Route::get('/consultations/manager/{consultation}', [ConsultationRequestController::class, 'managerShow'])->name('consultations.manager.show');
    Route::post('/consultations/{consultation}/assign', [ConsultationRequestController::class, 'assign'])->name('consultations.assign');
    Route::post('/consultations/{consultation}/assign-and-schedule', [ConsultationRequestController::class, 'assignAndSchedule'])->name('consultations.assign-and-schedule');
    Route::post('/consultations/{consultation}/send-invite', [ConsultationRequestController::class, 'sendMeetingInvite'])->name('consultations.send-invite');
    Route::post('/consultations/{consultation}/complete', [ConsultationRequestController::class, 'complete'])->name('consultations.complete');
    Route::post('/consultations/{consultation}/convert-to-project', [ConsultationRequestController::class, 'convertToProject'])->name('consultations.convert-to-project');

    // RESEARCH - Manager/Employee Routes
    Route::get('/research/manager', [ResearchRequestController::class, 'managerIndex'])->name('research.manager.index');
    Route::get('/research/manager/{research}', [ResearchRequestController::class, 'managerShow'])->name('research.manager.show');
    Route::post('/research/{research}/assign', [ResearchRequestController::class, 'assign'])->name('research.assign');
    Route::post('/research/{research}/complete', [ResearchRequestController::class, 'complete'])->name('research.complete');
    Route::post('/research/{research}/convert-to-project', [ResearchRequestController::class, 'convertToProject'])->name('research.convert-to-project');

    // IP REGISTRATION - Manager/Employee Routes
    Route::get('/ip/manager', [IpRegistrationController::class, 'managerIndex'])->name('ip.manager.index');
    Route::get('/ip/manager/{ip}', [IpRegistrationController::class, 'managerShow'])->name('ip.manager.show');
    Route::post('/ip/{ip}/assign', [IpRegistrationController::class, 'assign'])->name('ip.assign');
    Route::post('/ip/{ip}/confirm-meeting', [IpRegistrationController::class, 'confirmMeeting'])->name('ip.confirm-meeting');
    Route::post('/ip/{ip}/update-status', [IpRegistrationController::class, 'updateStatus'])->name('ip.update-status');
    Route::post('/ip/{ip}/convert-to-project', [IpRegistrationController::class, 'convertToProject'])->name('ip.convert-to-project');

    // COPYRIGHT - Manager/Employee Routes
    Route::get('/copyright/manager', [CopyrightRegistrationController::class, 'managerIndex'])->name('copyright.manager.index');
    Route::get('/copyright/manager/{copyright}', [CopyrightRegistrationController::class, 'managerShow'])->name('copyright.manager.show');
    Route::post('/copyright/{copyright}/assign', [CopyrightRegistrationController::class, 'assign'])->name('copyright.assign');
    Route::post('/copyright/{copyright}/confirm-meeting', [CopyrightRegistrationController::class, 'confirmMeeting'])->name('copyright.confirm-meeting');

    // PRICING TOOL
    Route::get('/pricing-tool', [\App\Http\Controllers\PricingToolController::class, 'index'])->name('pricing.tool');
    Route::get('/pricing/rules', [\App\Http\Controllers\PricingToolController::class, 'getRules'])->name('pricing.rules');
    Route::get('/quoting-tasks', [\App\Http\Controllers\PricingToolController::class, 'quotingTasks'])->name('pricing.quoting-tasks');
    Route::post('/projects/{project}/upload-quote', [\App\Http\Controllers\PricingToolController::class, 'uploadQuote'])->name('projects.quote.upload');
    Route::get('/projects/{project}/download-quote', [\App\Http\Controllers\PricingToolController::class, 'downloadQuote'])->name('projects.quote.download');
    Route::post('/copyright/{copyright}/update-status', [CopyrightRegistrationController::class, 'updateStatus'])->name('copyright.update-status');
    Route::post('/copyright/{copyright}/convert-to-project', [CopyrightRegistrationController::class, 'convertToProject'])->name('copyright.convert-to-project');

    // ============================================
    // MANAGER-ONLY ROUTES
    // ============================================
    Route::middleware(['is_manager'])->group(function () {

        // ENGAGEMENT SETTINGS — manager-tunable Impact-Points rulebook
        Route::get('/engagement/settings', [\App\Http\Controllers\EngagementSettingsController::class, 'edit'])->name('engagement.settings.edit');
        Route::put('/engagement/settings', [\App\Http\Controllers\EngagementSettingsController::class, 'update'])->name('engagement.settings.update');
        Route::post('/engagement/settings/reset', [\App\Http\Controllers\EngagementSettingsController::class, 'reset'])->name('engagement.settings.reset');

        // ENGAGEMENT POINTS (client loyalty program) — admin module (spec §14)
        Route::get('/engagement-points', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'dashboard'])->name('engagement.admin.dashboard');
        Route::get('/engagement-points/config', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'config'])->name('engagement.admin.config');
        Route::put('/engagement-points/rules/{rule}', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'updateRule'])->name('engagement.admin.rules.update');
        Route::put('/engagement-points/redemptions/{option}', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'updateRedemption'])->name('engagement.admin.redemptions.update');
        Route::put('/engagement-points/tiers/{tier}', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'updateTier'])->name('engagement.admin.tiers.update');
        Route::get('/engagement-points/accounts', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'accounts'])->name('engagement.admin.accounts');
        Route::get('/engagement-points/accounts/{account}', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'account'])->name('engagement.admin.account');
        Route::post('/engagement-points/accounts/{account}/adjust', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'adjust'])->name('engagement.admin.account.adjust');
        Route::get('/engagement-points/report', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'report'])->name('engagement.admin.report');

        // ENGAGEMENT POINTS — pending earns review (e.g. 6th+ accepted idea, spec §7)
        Route::get('/engagement-points/pending-earns', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'pendingEarns'])->name('engagement.admin.pending-earns');
        Route::post('/engagement-points/earns/{transaction}/approve', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'approveEarn'])->name('engagement.admin.earns.approve');
        Route::post('/engagement-points/earns/{transaction}/reject', [\App\Http\Controllers\Admin\EngagementAdminController::class, 'rejectEarn'])->name('engagement.admin.earns.reject');

        // PERFORMANCE TARGETS — manager control room (set/adjust, record month, trends, catalog)
        Route::get('/targets-admin', [\App\Http\Controllers\Admin\TargetController::class, 'index'])->name('targets.admin.index');
        Route::post('/targets-admin/set', [\App\Http\Controllers\Admin\TargetController::class, 'setTarget'])->name('targets.admin.set');
        Route::delete('/targets-admin/targets/{target}', [\App\Http\Controllers\Admin\TargetController::class, 'deleteTarget'])->name('targets.admin.delete');
        Route::post('/targets-admin/record-month', [\App\Http\Controllers\Admin\TargetController::class, 'recordMonth'])->name('targets.admin.record-month');
        Route::post('/targets-admin/carry-forward', [\App\Http\Controllers\Admin\TargetController::class, 'carryForward'])->name('targets.admin.carry-forward');
        Route::get('/targets-admin/staff/{user}', [\App\Http\Controllers\Admin\TargetController::class, 'show'])->name('targets.admin.show');
        Route::get('/targets-admin/metrics', [\App\Http\Controllers\Admin\TargetController::class, 'metrics'])->name('targets.admin.metrics');
        Route::post('/targets-admin/metrics', [\App\Http\Controllers\Admin\TargetController::class, 'storeMetric'])->name('targets.admin.metrics.store');
        Route::put('/targets-admin/metrics/{metric}', [\App\Http\Controllers\Admin\TargetController::class, 'updateMetric'])->name('targets.admin.metrics.update');

        // CAPACITY — manager control room (dashboard, engineers/skills, leave, assignments, submissions)
        Route::get('/capacity-admin', [\App\Http\Controllers\Admin\CapacityController::class, 'dashboard'])->name('capacity.admin.dashboard');
        Route::get('/capacity-admin/engineers', [\App\Http\Controllers\Admin\CapacityController::class, 'engineers'])->name('capacity.admin.engineers');
        Route::post('/capacity-admin/engineers', [\App\Http\Controllers\Admin\CapacityController::class, 'storeEngineer'])->name('capacity.admin.engineers.store');
        Route::put('/capacity-admin/engineers/{teamMember}', [\App\Http\Controllers\Admin\CapacityController::class, 'updateEngineer'])->name('capacity.admin.engineers.update');
        Route::get('/capacity-admin/leave', [\App\Http\Controllers\Admin\CapacityController::class, 'leave'])->name('capacity.admin.leave');
        Route::post('/capacity-admin/leave', [\App\Http\Controllers\Admin\CapacityController::class, 'storeLeave'])->name('capacity.admin.leave.store');
        Route::delete('/capacity-admin/leave/{leaveEntry}', [\App\Http\Controllers\Admin\CapacityController::class, 'deleteLeave'])->name('capacity.admin.leave.delete');
        Route::get('/capacity-admin/assignments', [\App\Http\Controllers\Admin\CapacityController::class, 'assignments'])->name('capacity.admin.assignments');
        Route::post('/capacity-admin/assignments', [\App\Http\Controllers\Admin\CapacityController::class, 'storeAssignment'])->name('capacity.admin.assignments.store');
        Route::delete('/capacity-admin/assignments/{assignment}', [\App\Http\Controllers\Admin\CapacityController::class, 'deleteAssignment'])->name('capacity.admin.assignments.delete');
        Route::get('/capacity-admin/submissions', [\App\Http\Controllers\Admin\CapacityController::class, 'submissions'])->name('capacity.admin.submissions');

        // ENGAGEMENT POINTS — claims review queue (spec §11)
        Route::get('/engagement-points/claims', [\App\Http\Controllers\Admin\EngagementClaimController::class, 'index'])->name('engagement.admin.claims.index');
        Route::post('/engagement-points/claims/{claim}/approve', [\App\Http\Controllers\Admin\EngagementClaimController::class, 'approve'])->name('engagement.admin.claims.approve');
        Route::post('/engagement-points/claims/{claim}/reject', [\App\Http\Controllers\Admin\EngagementClaimController::class, 'reject'])->name('engagement.admin.claims.reject');
        Route::get('/engagement-points/claims/{claim}/screenshot', [\App\Http\Controllers\Admin\EngagementClaimController::class, 'screenshot'])->name('engagement.admin.claims.screenshot');

        // DIRECT STAFF TASKS — assign / delete (manager only)
        Route::get('/staff-tasks/create', [\App\Http\Controllers\StaffTaskController::class, 'create'])->name('staff-tasks.create');
        Route::post('/staff-tasks', [\App\Http\Controllers\StaffTaskController::class, 'store'])->name('staff-tasks.store');
        Route::delete('/staff-tasks/{staffTask}', [\App\Http\Controllers\StaffTaskController::class, 'destroy'])->name('staff-tasks.destroy');

        // EXCEL/CSV IMPORTS — contacts, companies, opportunities, projects, tasks
        Route::get('/imports/{entity}/template', [\App\Http\Controllers\ImportController::class, 'template'])->name('imports.template');
        Route::get('/imports/{entity}', [\App\Http\Controllers\ImportController::class, 'form'])->name('imports.form');
        Route::post('/imports/{entity}', [\App\Http\Controllers\ImportController::class, 'store'])->name('imports.store');

        // CRM PIPELINE STAGES — manager-editable Kanban columns
        Route::get('/crm-stages', [\App\Http\Controllers\PipelineStageController::class, 'index'])->name('crm-stages.index');
        Route::post('/crm-stages', [\App\Http\Controllers\PipelineStageController::class, 'store'])->name('crm-stages.store');
        Route::put('/crm-stages/{stage}', [\App\Http\Controllers\PipelineStageController::class, 'update'])->name('crm-stages.update');
        Route::post('/crm-stages/{stage}/move', [\App\Http\Controllers\PipelineStageController::class, 'move'])->name('crm-stages.move');
        Route::delete('/crm-stages/{stage}', [\App\Http\Controllers\PipelineStageController::class, 'destroy'])->name('crm-stages.destroy');

        // Team Time Slots Overview (Manager Only)
        Route::get('/team-time-slots', [\App\Http\Controllers\TimeSlotController::class, 'teamSlots'])->name('time-slots.team-slots');
        Route::post('/team-time-slots/add', [\App\Http\Controllers\TimeSlotController::class, 'storeForEmployee'])->name('time-slots.store-for-employee');

        // PRICING ADMIN
        Route::get('/pricing-admin', [\App\Http\Controllers\PricingToolController::class, 'admin'])->name('pricing.admin');
        Route::post('/pricing-rules', [\App\Http\Controllers\PricingToolController::class, 'store'])->name('pricing.store');
        Route::put('/pricing-rules/{rule}', [\App\Http\Controllers\PricingToolController::class, 'update'])->name('pricing.update');
        Route::delete('/pricing-rules/{rule}', [\App\Http\Controllers\PricingToolController::class, 'destroy'])->name('pricing.destroy');

        // SCOPE PLANNER — manager-editable AI prompt templates
        Route::get('/scope-prompts', [\App\Http\Controllers\ScopePromptController::class, 'edit'])->name('scope-prompts.edit');
        Route::put('/scope-prompts', [\App\Http\Controllers\ScopePromptController::class, 'update'])->name('scope-prompts.update');
        Route::post('/scope-prompts/reset', [\App\Http\Controllers\ScopePromptController::class, 'reset'])->name('scope-prompts.reset');

        Route::get('/reports/financial', [FinancialReportController::class, 'index'])->name('reports.financial');

        // Projects moved to routes/projects.php

        // Team Invitation & Management
        Route::get('/team', [\App\Http\Controllers\TeamInvitationController::class, 'index'])->name('team.index');
        Route::get('/team/invite', [\App\Http\Controllers\TeamInvitationController::class, 'create'])->name('team.invite');
        Route::post('/team', [\App\Http\Controllers\TeamInvitationController::class, 'store'])->name('team.store');
        Route::delete('/team/{user}', [\App\Http\Controllers\TeamInvitationController::class, 'destroy'])->name('team.destroy');

        // Permissions & Roles Management
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Permissions\PermissionsController::class, 'index'])->name('index');
            Route::get('/roles', [\App\Http\Controllers\Permissions\PermissionsController::class, 'roles'])->name('roles');
            Route::get('/permissions', [\App\Http\Controllers\Permissions\PermissionsController::class, 'permissions'])->name('permissions');
            Route::get('/users', [\App\Http\Controllers\Permissions\PermissionsController::class, 'users'])->name('users');
            Route::get('/portal-clients', [\App\Http\Controllers\Permissions\PermissionsController::class, 'portalClients'])->name('portal-clients');

            // Account cleanup (bot/fake users) — clients only, staff protected
            Route::post('/users/bulk-delete', [\App\Http\Controllers\Permissions\PermissionsController::class, 'bulkDeleteUsers'])->name('users.bulk-delete');
            Route::post('/users/delete-unverified', [\App\Http\Controllers\Permissions\PermissionsController::class, 'deleteUnverifiedClients'])->name('users.delete-unverified');
            Route::delete('/users/{user}', [\App\Http\Controllers\Permissions\PermissionsController::class, 'destroyUser'])->name('users.destroy');

            // Manually approve/activate a pending account (sets status active + verifies email).
            Route::post('/users/{user}/activate', [\App\Http\Controllers\Permissions\PermissionsController::class, 'activateUser'])->name('users.activate');

            Route::post('/assign-role', [\App\Http\Controllers\Permissions\PermissionsController::class, 'assignRole'])->name('assign-role');
            Route::post('/remove-role', [\App\Http\Controllers\Permissions\PermissionsController::class, 'removeRole'])->name('remove-role');
            Route::post('/assign-permission', [\App\Http\Controllers\Permissions\PermissionsController::class, 'assignPermissionToRole'])->name('assign-permission');
            Route::post('/remove-permission', [\App\Http\Controllers\Permissions\PermissionsController::class, 'removePermissionFromRole'])->name('remove-permission');
            Route::post('/create-permission', [\App\Http\Controllers\Permissions\PermissionsController::class, 'createPermission'])->name('create-permission');
            Route::delete('/permissions/{permission}', [\App\Http\Controllers\Permissions\PermissionsController::class, 'deletePermission'])->name('delete-permission');
            Route::put('/roles/{role}/update-permissions', [\App\Http\Controllers\Permissions\PermissionsController::class, 'updateRolePermissions'])->name('update-role-permissions');
        });

        // Service Request Review Queue (Legacy)
        Route::get('/service-requests/review/queue', [ServiceRequestController::class, 'reviewQueue'])->name('service-requests.review-queue');
        Route::post('/service-requests/{serviceRequest}/review', [ServiceRequestController::class, 'review'])->name('service-requests.review');
        Route::post('/service-requests/{serviceRequest}/assign', [ServiceRequestController::class, 'assign'])->name('service-requests.assign');

        // ============================================
        // STEPPER SYSTEM (Advanced Feature)
        // ============================================
        Route::prefix('stepper')->name('stepper.')->group(function () {

            // Service Request Types Management
            Route::resource('service-types', ServiceRequestTypeController::class);
            Route::post('service-types/{serviceType}/steps', [ServiceRequestTypeController::class, 'storeStep'])->name('service-types.steps.store');
            Route::get('service-types/{serviceType}/steps/create', [ServiceRequestTypeController::class, 'createStep'])->name('service-types.steps.create');
            Route::put('service-types/{serviceType}/steps/reorder', [ServiceRequestTypeController::class, 'reorderSteps'])->name('service-types.steps.reorder');

            // Steps Management
            Route::get('steps/{step}', [ServiceRequestTypeController::class, 'editStep'])->name('steps.edit');
            Route::put('steps/{step}', [ServiceRequestTypeController::class, 'updateStep'])->name('steps.update');
            Route::delete('steps/{step}', [ServiceRequestTypeController::class, 'destroyStep'])->name('steps.destroy');

            // Form Fields Management
            Route::get('steps/{step}/fields/create', [StepFormFieldController::class, 'create'])->name('fields.create');
            Route::post('steps/{step}/fields', [StepFormFieldController::class, 'store'])->name('fields.store');
            Route::get('fields/{field}/edit', [StepFormFieldController::class, 'edit'])->name('fields.edit');
            Route::put('fields/{field}', [StepFormFieldController::class, 'update'])->name('fields.update');
            Route::delete('fields/{field}', [StepFormFieldController::class, 'destroy'])->name('fields.destroy');
            Route::put('steps/{step}/fields/reorder', [StepFormFieldController::class, 'reorder'])->name('fields.reorder');
        });
    });

    // ============================================
    // CLIENT STEPPER ROUTES (Advanced Feature)
    // ============================================
    Route::prefix('stepper')->name('stepper.client.')->group(function () {
        Route::get('/', [StepperServiceRequestController::class, 'index'])->name('index');
        Route::get('/create/{serviceType}', [StepperServiceRequestController::class, 'create'])->name('create');
        Route::post('/store/{serviceType}', [StepperServiceRequestController::class, 'store'])->name('store');
        Route::get('/show/{serviceRequest}', [StepperServiceRequestController::class, 'show'])->name('show');
        Route::get('/step/{serviceRequest}/{step}', [StepperServiceRequestController::class, 'showStep'])->name('step');
        Route::post('/step/{serviceRequest}/{step}', [StepperServiceRequestController::class, 'processStep'])->name('process-step');
    });

    // =======
});
