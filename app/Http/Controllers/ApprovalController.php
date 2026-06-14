<?php

namespace App\Http\Controllers;

use App\Services\ApprovalService;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index(ApprovalService $approvals)
    {
        $user = Auth::user();
        abort_unless($user->isManager() || $user->isProjectManager(), 403);

        return view('approvals.index', [
            'quotes' => $approvals->pendingQuotes(),
            'plans' => $user->isManager() ? $approvals->pendingPlans() : collect(),
            'isManager' => $user->isManager(),
        ]);
    }
}
