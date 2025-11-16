<?php

namespace App\Http\Controllers;

use App\Models\IdeaRequest;
use App\Models\ConsultationRequest;
use App\Models\ResearchRequest;
use App\Models\IpRegistration;
use App\Models\CopyrightRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientRequestsController extends Controller
{
    /**
     * Display all client requests across all services.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // if (!$user->isClient()) {
        //     abort(403);
        // }
        
        // Get filter parameters
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('type');
        
        // Collect all requests from all services
        $allRequests = collect();
        
        // Get Ideas
        $ideasQuery = IdeaRequest::where('user_id', $user->id);
        if ($statusFilter) {
            $ideasQuery->where('status', $statusFilter);
        }
        foreach ($ideasQuery->get() as $idea) {
            $allRequests->push([
                'id' => $idea->id,
                'type' => 'idea',
                'type_label' => 'Idea Generation',
                'type_icon' => 'lightbulb',
                'type_color' => '#f59e0b',
                'title' => $idea->title,
                'description' => $idea->description,
                'status' => $idea->status,
                'status_label' => $idea->getStatusLabel(),
                'status_color' => $idea->getStatusBadgeColor(),
                'created_at' => $idea->created_at,
                'updated_at' => $idea->updated_at,
                'view_url' => route('ideas.show', $idea),
                'has_quote' => $idea->final_quote ? true : false,
                'quote_amount' => $idea->final_quote,
                'assigned_to' => $idea->assignedTo?->name,
            ]);
        }
        
        // Get Consultations
        $consultationsQuery = ConsultationRequest::where('user_id', $user->id);
        if ($statusFilter) {
            $consultationsQuery->where('status', $statusFilter);
        }
        foreach ($consultationsQuery->get() as $consultation) {
            $allRequests->push([
                'id' => $consultation->id,
                'type' => 'consultation',
                'type_label' => 'Consultation',
                'type_icon' => 'comments',
                'type_color' => '#10b981',
                'title' => $consultation->title,
                'description' => $consultation->description,
                'status' => $consultation->status,
                'status_label' => $consultation->getStatusLabel(),
                'status_color' => $consultation->getStatusBadgeColor(),
                'created_at' => $consultation->created_at,
                'updated_at' => $consultation->updated_at,
                'view_url' => route('consultations.show', $consultation),
                'has_quote' => false,
                'quote_amount' => null,
                'assigned_to' => $consultation->assignedTo?->name,
                'meeting_date' => $consultation->meeting_scheduled_at,
            ]);
        }
        
        // Get Research
        $researchQuery = ResearchRequest::where('user_id', $user->id);
        if ($statusFilter) {
            $researchQuery->where('status', $statusFilter);
        }
        foreach ($researchQuery->get() as $research) {
            $allRequests->push([
                'id' => $research->id,
                'type' => 'research',
                'type_label' => 'Research & IP',
                'type_icon' => 'search',
                'type_color' => '#3b82f6',
                'title' => $research->title,
                'description' => $research->research_topic,
                'status' => $research->status,
                'status_label' => $research->getStatusLabel(),
                'status_color' => $research->getStatusBadgeColor(),
                'created_at' => $research->created_at,
                'updated_at' => $research->updated_at,
                'view_url' => route('research.show', $research),
                'has_quote' => false,
                'quote_amount' => null,
                'assigned_to' => $research->assignedTo?->name,
                'meeting_date' => $research->meeting_scheduled_at,
            ]);
        }
        
        // Get IP Registrations
        $ipQuery = IpRegistration::where('user_id', $user->id);
        if ($statusFilter) {
            $ipQuery->where('status', $statusFilter);
        }
        foreach ($ipQuery->get() as $ip) {
            $allRequests->push([
                'id' => $ip->id,
                'type' => 'ip',
                'type_label' => 'IP Registration',
                'type_icon' => 'file-contract',
                'type_color' => '#8b5cf6',
                'title' => $ip->title,
                'description' => $ip->ip_description,
                'status' => $ip->status,
                'status_label' => $ip->getStatusLabel(),
                'status_color' => $ip->getStatusBadgeColor(),
                'created_at' => $ip->created_at,
                'updated_at' => $ip->updated_at,
                'view_url' => route('ip.show', $ip),
                'has_quote' => false,
                'quote_amount' => null,
                'assigned_to' => $ip->assignedTo?->name,
                'meeting_date' => $ip->meeting_requested_at,
                'registration_number' => $ip->registration_number,
            ]);
        }
        
        // Get Copyrights
        $copyrightQuery = CopyrightRegistration::where('user_id', $user->id);
        if ($statusFilter) {
            $copyrightQuery->where('status', $statusFilter);
        }
        foreach ($copyrightQuery->get() as $copyright) {
            $allRequests->push([
                'id' => $copyright->id,
                'type' => 'copyright',
                'type_label' => 'Copyright Registration',
                'type_icon' => 'copyright',
                'type_color' => '#ec4899',
                'title' => $copyright->title,
                'description' => $copyright->work_description,
                'status' => $copyright->status,
                'status_label' => $copyright->getStatusLabel(),
                'status_color' => $copyright->getStatusBadgeColor(),
                'created_at' => $copyright->created_at,
                'updated_at' => $copyright->updated_at,
                'view_url' => route('copyright.show', $copyright),
                'has_quote' => false,
                'quote_amount' => null,
                'assigned_to' => $copyright->assignedTo?->name,
                'meeting_date' => $copyright->meeting_requested_at,
                'registration_number' => $copyright->copyright_number,
            ]);
        }
        
        // Filter by type if specified
        if ($typeFilter) {
            $allRequests = $allRequests->where('type', $typeFilter);
        }
        
        // Sort by most recent
        $allRequests = $allRequests->sortByDesc('updated_at');
        
        // Calculate summary stats
        $summary = [
            'total' => $allRequests->count(),
            'ideas' => $allRequests->where('type', 'idea')->count(),
            'consultations' => $allRequests->where('type', 'consultation')->count(),
            'research' => $allRequests->where('type', 'research')->count(),
            'ip' => $allRequests->where('type', 'ip')->count(),
            'copyright' => $allRequests->where('type', 'copyright')->count(),
            'pending' => $allRequests->whereIn('status', ['submitted', 'draft', 'nda_pending'])->count(),
            'in_progress' => $allRequests->whereIn('status', ['negotiation', 'assigned', 'in_progress', 'meeting_scheduled'])->count(),
            'completed' => $allRequests->where('status', 'completed')->count(),
        ];
        
        return view('client.requests', compact('allRequests', 'summary', 'statusFilter', 'typeFilter'));
    }
}
