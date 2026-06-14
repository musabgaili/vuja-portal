<?php

namespace App\Http\Controllers;

use App\Services\WorkloadService;
use Illuminate\Support\Facades\Auth;

class WorkloadController extends Controller
{
    public function index(WorkloadService $workload)
    {
        abort_unless(Auth::user()->isManager(), 403);

        return view('workload.index', $workload->grid());
    }
}
