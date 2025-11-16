<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Projects\MilestoneController;
use App\Http\Controllers\Projects\TaskController;
use App\Http\Controllers\Projects\ScopeChangeController;
use App\Http\Controllers\Projects\ExpenseController;
use App\Http\Controllers\Projects\FeedbackController;

/*
|--------------------------------------------------------------------------
| Project Routes
|--------------------------------------------------------------------------
|
| Routes for project management - both client and internal views
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ============================================
    // CLIENT PROJECT ROUTES
    // ============================================
    Route::prefix('projects')->name('projects.client.')->group(function () {
        Route::get('/', [ProjectController::class, 'clientIndex'])->name('index');
        Route::get('/show/{project}', [ProjectController::class, 'clientShow'])->name('show');
        Route::post('/{project}/comments', [ProjectController::class, 'addComment'])->name('add-comment');
        
        // Milestone Approval
        Route::post('/milestones/{milestone}/approve', [MilestoneController::class, 'clientApprove'])->name('milestones.approve');
        
        // Documents
        Route::get('/{project}/documents', [\App\Http\Controllers\Projects\DocumentController::class, 'index'])->name('documents.index');
        Route::post('/{project}/documents', [\App\Http\Controllers\Projects\DocumentController::class, 'store'])->name('documents.store');
        Route::get('/documents/{document}/download', [\App\Http\Controllers\Projects\DocumentController::class, 'download'])->name('documents.download');
        
        // Deliverables
        Route::get('/deliverables/{deliverable}/download', [\App\Http\Controllers\Projects\DeliverableController::class, 'download'])->name('deliverables.download');
        Route::post('/deliverables/{deliverable}/confirm', [\App\Http\Controllers\Projects\DeliverableController::class, 'confirmReceipt'])->name('deliverables.confirm');
        
        // Complaints
        Route::post('/{project}/complaints', [\App\Http\Controllers\Projects\ComplaintController::class, 'store'])->name('complaints.store');
        
        // Requests
        Route::post('/{project}/requests', [\App\Http\Controllers\Projects\RequestController::class, 'store'])->name('requests.store');
        
        // Scope Change Requests
        Route::get('/{project}/scope-change', [ScopeChangeController::class, 'create'])->name('scope-change.create');
        Route::post('/{project}/scope-change', [ScopeChangeController::class, 'store'])->name('scope-change.store');
        
        // Project Feedback
        Route::get('/{project}/feedback', [FeedbackController::class, 'create'])->name('feedback.create');
        Route::post('/{project}/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    });
    
    // ============================================
    // INTERNAL PROJECT ROUTES (Manager + Employees)
    // ============================================
    Route::prefix('internal/projects')->middleware(['is_internal'])->name('projects.')->group(function () {
        
        Route::get('/xx',function() {
            return 'test';
        });

        // Project Management
        Route::get('/', [ProjectController::class, 'managerIndex'])->name('manager.index');
        Route::get('/kanban', [ProjectController::class, 'kanban'])->name('kanban');
        Route::get('/show/{project}', [ProjectController::class, 'managerShow'])->name('manager.show');
        Route::put('/update/{project}', [ProjectController::class, 'update'])->name('update');
        Route::post('/{project}/update-status', [ProjectController::class, 'updateStatus'])->name('update-status');
        Route::post('/{project}/comments', [ProjectController::class, 'addComment'])->name('add-comment');
        
        // Milestones
        Route::post('/{project}/milestones', [MilestoneController::class, 'store'])->name('milestones.store');
        Route::put('/milestones/update/{milestone}', [MilestoneController::class, 'update'])->name('milestones.update');
        Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy'])->name('milestones.destroy');
        Route::post('/milestones/{milestone}/complete', [MilestoneController::class, 'markCompleted'])->name('milestones.complete');
        
        // Tasks
        Route::post('/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}/data', [TaskController::class, 'getData'])->name('tasks.data');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        
        // Scope Change Management
        Route::get('/scope-changes', [ScopeChangeController::class, 'index'])->name('scope-changes.index');
        Route::post('/scope-changes/{scopeChange}/approve', [ScopeChangeController::class, 'approve'])->name('scope-changes.approve');
        Route::post('/scope-changes/{scopeChange}/reject', [ScopeChangeController::class, 'reject'])->name('scope-changes.reject');
        
        // Expenses
        Route::get('/{project}/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/{project}/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        
        // Team Management
        Route::post('/{project}/team', [ProjectController::class, 'addTeamMember'])->name('team.add');
        Route::put('/team/{projectPerson}', [ProjectController::class, 'updateTeamMember'])->name('team.update');
        Route::delete('/team/{projectPerson}', [ProjectController::class, 'removeTeamMember'])->name('team.remove');
        
        // Documents
        Route::put('/documents/{document}', [\App\Http\Controllers\Projects\DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/documents/{document}', [\App\Http\Controllers\Projects\DocumentController::class, 'destroy'])->name('documents.destroy');
        
        // Deliverables
        Route::post('/{project}/deliverables', [\App\Http\Controllers\Projects\DeliverableController::class, 'store'])->name('deliverables.store');
        Route::delete('/deliverables/{deliverable}', [\App\Http\Controllers\Projects\DeliverableController::class, 'destroy'])->name('deliverables.destroy');
        
        // Complaints
        Route::post('/complaints/{complaint}/resolve', [\App\Http\Controllers\Projects\ComplaintController::class, 'resolve'])->name('complaints.resolve');
        
        // Requests
        Route::post('/requests/{request}/respond', [\App\Http\Controllers\Projects\RequestController::class, 'respond'])->name('requests.respond');
   
        // ============================================
        // MANAGER-ONLY PROJECT ROUTES
        // ============================================
        Route::middleware(['is_internal'])->group(function () {
            Route::get('/create', [ProjectController::class, 'create'])->name('create');
            Route::post('/', [ProjectController::class, 'store'])->name('store');
            Route::delete('/destroy/{project}', [ProjectController::class, 'destroy'])->name('destroy');
        });
    });
    
    
});

