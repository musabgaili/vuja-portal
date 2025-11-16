<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\IdeaRequest;
use App\Models\ConsultationRequest;
use App\Models\ResearchRequest;
use App\Models\IpRegistration;
use App\Models\CopyrightRegistration;
use App\Models\Project;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the appropriate dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Redirect based on user type
        if ($user->type === 'internal') {
            return redirect()->route('internal.dashboard');
        }
        
        return redirect()->route('client.dashboard');
    }

    /**
     * Show the client dashboard.
     */
    public function clientDashboard()
    {
        $user = Auth::user();
        
        // Calculate statistics using queries (not collections)
        $stats = [
            // Projects stats
            'active_projects' => IdeaRequest::where('user_id', $user->id)
                                           ->whereIn('status', ['approved', 'in_progress'])
                                           ->count() +
                                ResearchRequest::where('user_id', $user->id)
                                              ->where('status', 'in_progress')
                                              ->count(),
                                              
            'pending_projects' => IdeaRequest::where('user_id', $user->id)
                                            ->whereIn('status', ['submitted', 'negotiation', 'quoted'])
                                            ->count() +
                                 ConsultationRequest::where('user_id', $user->id)
                                                   ->whereIn('status', ['submitted', 'filtered'])
                                                   ->count() +
                                 ResearchRequest::where('user_id', $user->id)
                                               ->whereIn('status', ['submitted', 'nda_pending'])
                                               ->count(),
                                               
            'completed_projects' => IdeaRequest::where('user_id', $user->id)->where('status', 'completed')->count() +
                                   ConsultationRequest::where('user_id', $user->id)->where('status', 'completed')->count() +
                                   ResearchRequest::where('user_id', $user->id)->where('status', 'completed')->count() +
                                   IpRegistration::where('user_id', $user->id)->where('status', 'completed')->count() +
                                   CopyrightRegistration::where('user_id', $user->id)->where('status', 'completed')->count(),
            
            // Service requests stats
            'requests_in_review' => IdeaRequest::where('user_id', $user->id)
                                              ->whereIn('status', ['submitted', 'negotiation'])
                                              ->count() +
                                   ConsultationRequest::where('user_id', $user->id)
                                                     ->whereIn('status', ['submitted', 'filtered', 'assigned'])
                                                     ->count() +
                                   ResearchRequest::where('user_id', $user->id)
                                                 ->whereIn('status', ['submitted', 'nda_pending', 'nda_signed'])
                                                 ->count(),
                                                 
            'requests_approved' => IdeaRequest::where('user_id', $user->id)
                                             ->whereIn('status', ['approved', 'in_progress'])
                                             ->count() +
                                  ConsultationRequest::where('user_id', $user->id)
                                                    ->where('status', 'meeting_sent')
                                                    ->count() +
                                  ResearchRequest::where('user_id', $user->id)
                                                ->where('status', 'in_progress')
                                                ->count(),
            
            // Meetings stats (using whereDate properly on queries)
            'meetings_this_week' => ConsultationRequest::where('user_id', $user->id)
                                                      ->whereBetween('meeting_scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                                                      ->count() +
                                   ResearchRequest::where('user_id', $user->id)
                                                 ->whereBetween('meeting_scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                                                 ->count() +
                                   IpRegistration::where('user_id', $user->id)
                                                ->whereBetween('meeting_requested_at', [now()->startOfWeek(), now()->endOfWeek()])
                                                ->count() +
                                   CopyrightRegistration::where('user_id', $user->id)
                                                       ->whereBetween('meeting_requested_at', [now()->startOfWeek(), now()->endOfWeek()])
                                                       ->count(),
                                                       
            'meetings_today' => ConsultationRequest::where('user_id', $user->id)
                                                  ->whereDate('meeting_scheduled_at', today())
                                                  ->count() +
                               ResearchRequest::where('user_id', $user->id)
                                             ->whereDate('meeting_scheduled_at', today())
                                             ->count() +
                               IpRegistration::where('user_id', $user->id)
                                            ->whereDate('meeting_requested_at', today())
                                            ->count() +
                               CopyrightRegistration::where('user_id', $user->id)
                                                   ->whereDate('meeting_requested_at', today())
                                                   ->count(),
            
            // AI tokens
            'total_tokens' => IdeaRequest::where('user_id', $user->id)->sum('tokens_used'),
            'ai_assessments' => IdeaRequest::where('user_id', $user->id)->whereNotNull('ai_assessment_data')->count(),
        ];
        
        // Get recent activities - now using queries
        $recentActivities = collect();
        
        $recentIdeas = IdeaRequest::where('user_id', $user->id)
                                  ->latest('updated_at')
                                  ->take(3)
                                  ->get();
        foreach ($recentIdeas as $idea) {
            $recentActivities->push([
                'type' => 'idea',
                'icon' => 'lightbulb',
                'color' => 'warning',
                'title' => $idea->title,
                'status' => $idea->getStatusLabel(),
                'time' => $idea->updated_at,
            ]);
        }
        
        $recentConsultations = ConsultationRequest::where('user_id', $user->id)
                                                  ->latest('updated_at')
                                                  ->take(3)
                                                  ->get();
        foreach ($recentConsultations as $consultation) {
            $recentActivities->push([
                'type' => 'consultation',
                'icon' => 'comments',
                'color' => 'success',
                'title' => $consultation->title,
                'status' => $consultation->getStatusLabel(),
                'time' => $consultation->updated_at,
            ]);
        }
        
        $recentResearch = ResearchRequest::where('user_id', $user->id)
                                        ->latest('updated_at')
                                        ->take(2)
                                        ->get();
        foreach ($recentResearch as $r) {
            $recentActivities->push([
                'type' => 'research',
                'icon' => 'search',
                'color' => 'info',
                'title' => $r->title,
                'status' => $r->getStatusLabel(),
                'time' => $r->updated_at,
            ]);
        }
        
        $recentActivities = $recentActivities->sortByDesc('time')->take(10);
        
        // Get active projects
        $activeProjects = collect();
        
        $activeIdeas = IdeaRequest::where('user_id', $user->id)
                                  ->whereIn('status', ['approved', 'in_progress'])
                                  ->take(5)
                                  ->get();
        foreach ($activeIdeas as $idea) {
            $activeProjects->push([
                'title' => $idea->title,
                'description' => 'Idea Generation',
                'status' => $idea->getStatusLabel(),
                'progress' => $this->calculateProgress($idea->status, [
                    'approved' => 60,
                    'in_progress' => 85,
                    'completed' => 100
                ]),
                'icon' => 'lightbulb',
                'color' => 'primary',
            ]);
        }
        
        $activeResearch = ResearchRequest::where('user_id', $user->id)
                                        ->where('status', 'in_progress')
                                        ->take(3)
                                        ->get();
        foreach ($activeResearch as $r) {
            $activeProjects->push([
                'title' => $r->title,
                'description' => 'Research & IP',
                'status' => $r->getStatusLabel(),
                'progress' => 70,
                'icon' => 'search',
                'color' => 'success',
            ]);
        }
        
        return view('client.dashboard', compact('stats', 'recentActivities', 'activeProjects'));
    }

    /**
     * Show the internal dashboard for employees and managers.
     */
    public function internalDashboard()
    {
        $user = Auth::user();
        
        if ($user->isManager()) {
            return $this->managerDashboard();
        }
        
        return $this->employeeDashboard();
    }

    /**
     * Employee dashboard - shows only assigned tasks
     */
    protected function employeeDashboard()
    {
        $user = Auth::user();
        
        // Count only projects employee is assigned to
        $assignedProjectsCount = Project::whereHas('projectPeople', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();
        
        $stats = [
            'total_assigned' => IdeaRequest::where('assigned_to', $user->id)->count() +
                               ConsultationRequest::where('assigned_to', $user->id)->count() +
                               ResearchRequest::where('assigned_to', $user->id)->count(),
            'in_progress' => IdeaRequest::where('assigned_to', $user->id)->where('status', 'in_progress')->count() +
                            ResearchRequest::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
            'meetings_today' => Meeting::where('team_member_id', $user->id)
                              ->whereDate('scheduled_at', today())->count(),
            'assigned_projects' => $assignedProjectsCount,
        ];

        $assignedIdeas = IdeaRequest::where('assigned_to', $user->id)
            ->with('user')->latest()->take(5)->get();
        
        $assignedConsultations = ConsultationRequest::where('assigned_to', $user->id)
            ->with('user')->latest()->take(5)->get();
        
        $assignedResearch = ResearchRequest::where('assigned_to', $user->id)
            ->with('user')->latest()->take(5)->get();
        
        $upcomingMeetings = Meeting::where('team_member_id', $user->id)
            ->with('client')
            ->where('scheduled_at', '>=', now())
            ->where('scheduled_at', '<=', now()->addWeek())
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        return view('internal.employee-dashboard', compact(
            'stats', 'assignedIdeas', 'assignedConsultations', 'assignedResearch', 'upcomingMeetings'
        ));
    }

    /**
     * Manager dashboard - shows all new requests with 5 tables
     */
    protected function managerDashboard()
    {
        $stats = [
            'new_requests' => IdeaRequest::whereIn('status', ['submitted'])->count() +
                             ConsultationRequest::whereIn('status', ['submitted'])->count() +
                             ResearchRequest::whereIn('status', ['submitted'])->count() +
                             IpRegistration::whereIn('status', ['submitted'])->count() +
                             CopyrightRegistration::whereIn('status', ['submitted'])->count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'team_count' => User::where('type', 'internal')->where('status', 'active')->count(),
            'meetings_today' => Meeting::whereDate('scheduled_at', today())->count(),
            'total_clients' => User::where('type', 'client')->count(),
            'completed_month' => IdeaRequest::where('status', 'completed')
                                ->whereMonth('updated_at', now()->month)->count() +
                                ConsultationRequest::where('status', 'completed')
                                ->whereMonth('updated_at', now()->month)->count(),
        ];

        // Get latest 5 from each service
        $newIdeas = IdeaRequest::with('user')
            ->whereIn('status', ['submitted', 'ai_assessment', 'negotiation'])
            ->latest()->take(5)->get();
        
        $newConsultations = ConsultationRequest::with('user')
            ->whereIn('status', ['submitted', 'filtered'])
            ->latest()->take(5)->get();
        
        $newResearch = ResearchRequest::with('user')
            ->whereIn('status', ['submitted', 'nda_pending'])
            ->latest()->take(5)->get();
        
        $newIpRegistrations = IpRegistration::with('user')
            ->whereIn('status', ['submitted', 'meeting_booked'])
            ->latest()->take(5)->get();
        
        $newCopyrights = CopyrightRegistration::with('user')
            ->whereIn('status', ['submitted', 'meeting_booked'])
            ->latest()->take(5)->get();

        return view('internal.manager-dashboard', compact(
            'stats', 'newIdeas', 'newConsultations', 'newResearch', 'newIpRegistrations', 'newCopyrights'
        ));
    }
    
    /**
     * Calculate progress percentage based on status.
     */
    private function calculateProgress($status, $progressMap)
    {
        return $progressMap[$status] ?? 50;
    }
}