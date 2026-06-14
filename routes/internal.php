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

    // CONTROL TOWER — owner project-health traffic light + risk alerts (manager)
    Route::get('/control-tower', [ControlTowerController::class, 'index'])->name('control-tower.index');

    // WORKLOAD HEATMAP — who is busy / who is idle (manager)
    Route::get('/workload', [\App\Http\Controllers\WorkloadController::class, 'index'])->name('workload.index');

    // WEEKLY STRATEGIC PLANNER (employees plan; managers review/oversee)
    Route::get('/weekly-planner', [WeeklyPlannerController::class, 'index'])->name('weekly-planner.index');
    Route::post('/weekly-planner', [WeeklyPlannerController::class, 'store'])->name('weekly-planner.store');
    Route::get('/weekly-planner/review', [WeeklyPlannerController::class, 'review'])->name('weekly-planner.review');
    Route::get('/weekly-planner/presence', [WeeklyPlannerController::class, 'presence'])->name('weekly-planner.presence');
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

    // CONSULTATIONS - Manager/Employee Routes
    Route::get('/consultations/manager', [ConsultationRequestController::class, 'managerIndex'])->name('consultations.manager.index');
    Route::get('/consultations/manager/{consultation}', [ConsultationRequestController::class, 'managerShow'])->name('consultations.manager.show');
    Route::post('/consultations/{consultation}/assign', [ConsultationRequestController::class, 'assign'])->name('consultations.assign');
    Route::post('/consultations/{consultation}/send-invite', [ConsultationRequestController::class, 'sendMeetingInvite'])->name('consultations.send-invite');
    Route::post('/consultations/{consultation}/complete', [ConsultationRequestController::class, 'complete'])->name('consultations.complete');

    // RESEARCH - Manager/Employee Routes
    Route::get('/research/manager', [ResearchRequestController::class, 'managerIndex'])->name('research.manager.index');
    Route::get('/research/manager/{research}', [ResearchRequestController::class, 'managerShow'])->name('research.manager.show');
    Route::post('/research/{research}/assign', [ResearchRequestController::class, 'assign'])->name('research.assign');
    Route::post('/research/{research}/complete', [ResearchRequestController::class, 'complete'])->name('research.complete');

    // IP REGISTRATION - Manager/Employee Routes
    Route::get('/ip/manager', [IpRegistrationController::class, 'managerIndex'])->name('ip.manager.index');
    Route::get('/ip/manager/{ip}', [IpRegistrationController::class, 'managerShow'])->name('ip.manager.show');
    Route::post('/ip/{ip}/assign', [IpRegistrationController::class, 'assign'])->name('ip.assign');
    Route::post('/ip/{ip}/confirm-meeting', [IpRegistrationController::class, 'confirmMeeting'])->name('ip.confirm-meeting');
    Route::post('/ip/{ip}/update-status', [IpRegistrationController::class, 'updateStatus'])->name('ip.update-status');

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

    // ============================================
    // MANAGER-ONLY ROUTES
    // ============================================
    Route::middleware(['is_manager'])->group(function () {

        // Team Time Slots Overview (Manager Only)
        Route::get('/team-time-slots', [\App\Http\Controllers\TimeSlotController::class, 'teamSlots'])->name('time-slots.team-slots');

        // PRICING ADMIN
        Route::get('/pricing-admin', [\App\Http\Controllers\PricingToolController::class, 'admin'])->name('pricing.admin');
        Route::post('/pricing-rules', [\App\Http\Controllers\PricingToolController::class, 'store'])->name('pricing.store');
        Route::put('/pricing-rules/{rule}', [\App\Http\Controllers\PricingToolController::class, 'update'])->name('pricing.update');
        Route::delete('/pricing-rules/{rule}', [\App\Http\Controllers\PricingToolController::class, 'destroy'])->name('pricing.destroy');
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
