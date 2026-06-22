<?php

namespace Database\Seeders;

use App\Actions\Projects\CreateProjectAction;
use App\Actions\Projects\UpdateProjectProgressAction;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ConsultationRequest;
use App\Models\CopyrightRegistration;
use App\Models\IdeaRequest;
use App\Models\IpRegistration;
use App\Models\Meeting;
use App\Models\ProjectComplaint;
use App\Models\ProjectDeliverable;
use App\Models\ProjectDocument;
use App\Models\ProjectExpense;
use App\Models\ProjectFeedback;
use App\Models\ProjectMilestone;
use App\Models\ProjectRequest;
use App\Models\ProjectScopeChange;
use App\Models\ProjectTask;
use App\Models\ResearchRequest;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestStep;
use App\Models\ServiceRequestType;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Rich demo data for local/staging QA (not run in production from DatabaseSeeder).
 *
 * Logins (password for all: 12345678):
 * - manager@vujade.com, pm@vujade.com, employee@vujade.com (from UserSeeder)
 * - client@vujade.com (from UserSeeder)
 * - client2@vujade.com, client3@vujade.com, employee2@vujade.com (created here)
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Never seed demo accounts/data on production, even via a direct
        // `db:seed --class=DemoDataSeeder` invocation (shared "12345678" password).
        if (app()->environment('production')) {
            $this->command?->warn('DemoDataSeeder skipped on production.');

            return;
        }

        $manager = User::where('email', 'manager@vujade.com')->first();
        $pm = User::where('email', 'pm@vujade.com')->first();
        $employee = User::where('email', 'employee@vujade.com')->first();
        $client = User::where('email', 'client@vujade.com')->first();

        if (! $manager || ! $pm || ! $employee || ! $client) {
            $this->command?->error('DemoDataSeeder requires UserSeeder users (manager, pm, employee, client).');

            return;
        }

        $client2 = User::create([
            'name' => 'Alex Rivera (Client)',
            'email' => 'client2@vujade.com',
            'phone' => '+1234567801',
            'password' => '12345678',
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
            'type' => 'client',
            'email_verified_at' => now(),
        ]);
        $client2->assignRole('client');

        $client3 = User::create([
            'name' => 'Sam Chen (Client)',
            'email' => 'client3@vujade.com',
            'phone' => '+1234567802',
            'password' => '12345678',
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
            'type' => 'client',
            'email_verified_at' => now(),
        ]);
        $client3->assignRole('client');

        $employee2 = User::create([
            'name' => 'Jordan Internal',
            'email' => 'employee2@vujade.com',
            'phone' => '+1234567803',
            'password' => '12345678',
            'role' => UserRole::EMPLOYEE,
            'status' => UserStatus::ACTIVE,
            'type' => 'internal',
            'email_verified_at' => now(),
        ]);
        $employee2->assignRole('employee');

        $ideaType = ServiceRequestType::where('slug', 'idea-generation')->first();
        $consultType = ServiceRequestType::where('slug', 'expert-consultation')->first();
        $ideaStep1 = $ideaType ? ServiceRequestStep::where('service_request_type_id', $ideaType->id)->where('step_order', 1)->first() : null;

        $create = app(CreateProjectAction::class);

        $projectAlpha = $create->execute([
            'client_id' => $client->id,
            'title' => 'Demo: Brand refresh & website',
            'description' => 'End-to-end brand refresh with marketing site and component library for QA.',
            'scope' => 'Design system, 8 page templates, CMS handoff.',
            'status' => 'in_progress',
            'budget' => 85000,
            'spent' => 12000,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'project_manager_id' => $pm->id,
            'team_members' => [$employee->id, $employee2->id],
        ]);

        $projectBeta = $create->execute([
            'client_id' => $client2->id,
            'title' => 'Demo: Mobile app MVP (quoted)',
            'description' => 'Quoted project waiting for client signature — use to test quoted pipeline.',
            'scope' => 'iOS/Android MVP, auth, payments.',
            'status' => 'quoted',
            'budget' => 120000,
            'start_date' => now()->addMonth()->toDateString(),
            'end_date' => now()->addMonths(7)->toDateString(),
            'project_manager_id' => $pm->id,
            'team_members' => [$employee->id],
        ]);

        $projectGamma = $create->execute([
            'client_id' => $client3->id,
            'title' => 'Demo: Completed analytics rollout',
            'description' => 'Historical completed project for dashboards and client feedback views.',
            'scope' => 'Warehouse analytics, dashboards, training.',
            'status' => 'completed',
            'budget' => 45000,
            'spent' => 43800,
            'completion_percentage' => 100,
            'start_date' => now()->subYear()->toDateString(),
            'end_date' => now()->subMonths(3)->toDateString(),
            'actual_end_date' => now()->subMonths(3)->toDateString(),
            'project_manager_id' => $pm->id,
            'team_members' => [$employee2->id],
        ]);

        // --- Milestones & tasks (Alpha) ---
        $mAlpha1 = ProjectMilestone::create([
            'project_id' => $projectAlpha->id,
            'title' => 'Discovery & IA',
            'description' => 'Workshops and information architecture.',
            'milestone_order' => 1,
            'status' => 'in_progress',
            'due_date' => now()->addWeeks(2),
            'completion_percentage' => 65,
            'client_approved' => true,
            'client_approved_at' => now()->subWeek(),
            'approval_note' => 'Looks good — proceed.',
        ]);

        $mAlpha2 = ProjectMilestone::create([
            'project_id' => $projectAlpha->id,
            'title' => 'UI design & handoff',
            'description' => 'High fidelity screens and tokens.',
            'milestone_order' => 2,
            'status' => 'pending',
            'due_date' => now()->addWeeks(6),
            'completion_percentage' => 0,
        ]);

        ProjectTask::create([
            'project_id' => $projectAlpha->id,
            'milestone_id' => $mAlpha1->id,
            'title' => 'Stakeholder interviews',
            'description' => 'Record notes in shared doc.',
            'status' => 'completed',
            'priority' => 'high',
            'assigned_to' => $employee->id,
            'created_by' => $pm->id,
            'due_date' => now()->subWeek(),
            'completed_at' => now()->subWeek(),
            'estimated_hours' => 8,
            'actual_hours' => 10,
        ]);

        ProjectTask::create([
            'project_id' => $projectAlpha->id,
            'milestone_id' => $mAlpha1->id,
            'title' => 'Sitemap draft',
            'description' => 'First pass sitemap.',
            'status' => 'in_progress',
            'priority' => 'medium',
            'assigned_to' => $employee2->id,
            'created_by' => $pm->id,
            'due_date' => now()->addDays(5),
            'estimated_hours' => 12,
        ]);

        ProjectTask::create([
            'project_id' => $projectAlpha->id,
            'milestone_id' => null,
            'title' => 'Weekly internal sync',
            'description' => 'Ungrouped task for board coverage.',
            'status' => 'todo',
            'priority' => 'low',
            'assigned_to' => $employee->id,
            'created_by' => $manager->id,
            'due_date' => now()->addDay(),
        ]);

        // Beta: one milestone
        $mBeta1 = ProjectMilestone::create([
            'project_id' => $projectBeta->id,
            'title' => 'Proposal acceptance',
            'description' => 'Awaiting client sign-off on SOW.',
            'milestone_order' => 1,
            'status' => 'pending',
            'due_date' => now()->addWeeks(3),
            'completion_percentage' => 0,
        ]);

        ProjectTask::create([
            'project_id' => $projectBeta->id,
            'milestone_id' => $mBeta1->id,
            'title' => 'Technical spike: push',
            'description' => null,
            'status' => 'review',
            'priority' => 'urgent',
            'assigned_to' => $employee->id,
            'created_by' => $pm->id,
            'due_date' => now()->addDays(10),
        ]);

        // Gamma: completed milestones
        $mGamma1 = ProjectMilestone::create([
            'project_id' => $projectGamma->id,
            'title' => 'Data pipeline',
            'description' => 'ETL to warehouse.',
            'milestone_order' => 1,
            'status' => 'completed',
            'due_date' => now()->subMonths(6),
            'completed_at' => now()->subMonths(6),
            'completion_percentage' => 100,
            'client_approved' => true,
            'client_approved_at' => now()->subMonths(6),
        ]);

        $mGamma2 = ProjectMilestone::create([
            'project_id' => $projectGamma->id,
            'title' => 'Training & handover',
            'description' => null,
            'milestone_order' => 2,
            'status' => 'completed',
            'due_date' => now()->subMonths(3),
            'completed_at' => now()->subMonths(3),
            'completion_percentage' => 100,
            'client_approved' => true,
            'client_approved_at' => now()->subMonths(3),
        ]);

        ProjectTask::create([
            'project_id' => $projectGamma->id,
            'milestone_id' => $mGamma1->id,
            'title' => 'Build nightly loads',
            'description' => null,
            'status' => 'completed',
            'priority' => 'medium',
            'assigned_to' => $employee2->id,
            'created_by' => $pm->id,
            'due_date' => now()->subMonths(6),
            'completed_at' => now()->subMonths(6),
        ]);

        // --- Project satellites (Alpha) ---
        ProjectRequest::create([
            'project_id' => $projectAlpha->id,
            'client_id' => $client->id,
            'subject' => 'Can we add a blog section?',
            'request' => 'We would like a simple CMS-driven blog before launch.',
            'status' => 'open',
        ]);

        ProjectRequest::create([
            'project_id' => $projectAlpha->id,
            'client_id' => $client->id,
            'subject' => 'Logo usage on merch',
            'request' => 'Need export pack for print vendor.',
            'status' => 'reviewing',
            'handled_by' => $pm->id,
            'handled_at' => now()->subDays(2),
            'response' => 'We will add a merch pack to the next deliverable.',
        ]);

        ProjectScopeChange::create([
            'project_id' => $projectAlpha->id,
            'requested_by' => $client->id,
            'title' => 'Extra landing page',
            'description' => 'Add one campaign landing page.',
            'justification' => 'Paid media launch moved up.',
            'status' => 'pending',
        ]);

        ProjectScopeChange::create([
            'project_id' => $projectAlpha->id,
            'requested_by' => $client->id,
            'title' => 'Remove newsletter integration',
            'description' => 'Use external tool instead.',
            'status' => 'approved',
            'reviewed_by' => $manager->id,
            'reviewed_at' => now()->subWeek(),
            'review_notes' => 'Approved — adjust SOW offline.',
        ]);

        ProjectExpense::create([
            'project_id' => $projectAlpha->id,
            'logged_by' => $pm->id,
            'title' => 'Figma seats (1 month)',
            'description' => null,
            'amount' => 45.00,
            'category' => 'software',
            'expense_date' => now()->subDays(5),
        ]);

        ProjectDocument::create([
            'project_id' => $projectAlpha->id,
            'uploaded_by' => $employee->id,
            'title' => 'IA outline (demo)',
            'file_path' => 'demo/ia-outline.pdf',
            'file_name' => 'ia-outline.pdf',
            'tag' => 'planning',
            'comment' => 'Placeholder path for local seeding.',
            'file_type' => 'application/pdf',
            'file_size' => 102400,
        ]);

        ProjectDeliverable::create([
            'project_id' => $projectAlpha->id,
            'uploaded_by' => $pm->id,
            'title' => 'Design tokens JSON',
            'description' => 'Demo deliverable file.',
            'file_path' => 'demo/tokens.json',
            'client_confirmed' => false,
        ]);

        ProjectComplaint::create([
            'project_id' => $projectAlpha->id,
            'client_id' => $client->id,
            'subject' => 'Delay on milestone review',
            'complaint' => 'We waited 4 days for feedback on the sitemap.',
            'status' => 'open',
        ]);

        ProjectComplaint::create([
            'project_id' => $projectAlpha->id,
            'client_id' => $client->id,
            'subject' => 'Resolved: meeting notes',
            'complaint' => 'Notes were missing from last workshop.',
            'status' => 'resolved',
            'resolved_by' => $pm->id,
            'resolved_at' => now()->subDays(4),
            'resolution_note' => 'Notes uploaded to documents tab.',
        ]);

        ProjectFeedback::create([
            'project_id' => $projectGamma->id,
            'client_id' => $client3->id,
            'rating' => 5,
            'feedback' => 'Great collaboration and clear reporting throughout.',
            'communication_rating' => 5,
            'quality_rating' => 5,
            'timeline_rating' => 4,
            'would_recommend' => true,
        ]);

        // --- Service requests (typed flows) ---
        IdeaRequest::create([
            'user_id' => $client->id,
            'client_type' => 'company',
            'idea_status' => 'ready',
            'title' => 'Demo: Smart inventory kiosk',
            'description' => 'Retail kiosk that suggests restocks from shelf cameras.',
            'target_market' => 'Mid-size retail chains',
            'status' => 'submitted',
            'assigned_to' => $pm->id,
            'manager_id' => $manager->id,
        ]);

        IdeaRequest::create([
            'user_id' => $client2->id,
            'client_type' => 'individual',
            'idea_status' => 'concept_only',
            'title' => 'Demo: Neighborhood compost pickup',
            'description' => 'Subscription compost pickup with route optimization.',
            'status' => 'negotiation',
            'negotiation_notes' => 'Client asked for phased pricing.',
            'initial_quote' => 15000,
            'final_quote' => 13500,
            'assigned_to' => $manager->id,
            'manager_id' => $manager->id,
        ]);

        IdeaRequest::create([
            'user_id' => $client3->id,
            'client_type' => 'company',
            'idea_status' => 'running_project',
            'title' => 'Demo: HR onboarding copilot',
            'description' => 'Internal HR assistant for policy Q&A.',
            'status' => 'ai_assessment',
            'ai_assessment_data' => ['summary' => 'Strong fit for enterprise HR.', 'risk' => 'low'],
            'tokens_used' => 120,
            'assigned_to' => $employee->id,
            'manager_id' => $manager->id,
        ]);

        $consultForMeeting = ConsultationRequest::create([
            'user_id' => $client->id,
            'category' => 'Technology Consulting',
            'title' => 'Demo: Architecture review',
            'description' => 'Need a second opinion on microservices boundaries.',
            'specific_questions' => 'Kafka vs SNS for our scale?',
            'status' => 'meeting_scheduled',
            'assigned_to' => $employee->id,
            'meeting_scheduled_at' => now()->addDays(3)->setTime(14, 0),
            'meeting_link' => 'https://example.com/meet/demo-consult',
        ]);

        ConsultationRequest::create([
            'user_id' => $client2->id,
            'category' => 'Business Strategy',
            'title' => 'Demo: Go-to-market for B2B SaaS',
            'description' => 'Pricing and channel strategy.',
            'status' => 'assigned',
            'assigned_to' => $pm->id,
        ]);

        ResearchRequest::create([
            'user_id' => $client->id,
            'title' => 'Demo: Competitor pricing study',
            'research_topic' => 'EU competitors in legaltech',
            'research_details' => 'Focus on SMB segment.',
            'status' => 'in_progress',
            'assigned_to' => $employee2->id,
        ]);

        ResearchRequest::create([
            'user_id' => $client3->id,
            'title' => 'Demo: Patent landscape',
            'research_topic' => 'Computer vision in agriculture',
            'status' => 'meeting_scheduled',
            'meeting_scheduled_at' => now()->addWeek(),
            'meeting_link' => 'https://example.com/meet/demo-research',
            'assigned_to' => $pm->id,
        ]);

        IpRegistration::create([
            'user_id' => $client2->id,
            'title' => 'Demo: Trademark — Vuja Labs wordmark',
            'ip_description' => 'Wordmark and stylized logo for software division.',
            'ip_type' => 'Trademark',
            'status' => 'meeting_confirmed',
            'meeting_requested_at' => now()->subWeek(),
            'meeting_confirmed_at' => now()->subDays(3),
            'meeting_link' => 'https://example.com/meet/demo-ip',
            'assigned_to' => $manager->id,
        ]);

        IpRegistration::create([
            'user_id' => $client->id,
            'title' => 'Demo: Patent application draft',
            'ip_description' => 'Novel sorting algorithm for logistics.',
            'ip_type' => 'Patent',
            'status' => 'documentation',
            'assigned_to' => $employee->id,
        ]);

        CopyrightRegistration::create([
            'user_id' => $client3->id,
            'title' => 'Demo: Training video series',
            'work_description' => 'Twelve-module internal training series.',
            'work_type' => 'Software',
            'status' => 'filing',
            'assigned_to' => $pm->id,
        ]);

        CopyrightRegistration::create([
            'user_id' => $client->id,
            'title' => 'Demo: E-book workbook',
            'work_description' => 'Companion workbook PDF.',
            'work_type' => 'Literary',
            'status' => 'submitted',
            'assigned_to' => $employee2->id,
        ]);

        // --- Stepper service_requests ---
        if ($ideaType && $ideaStep1) {
            ServiceRequest::create([
                'user_id' => $client->id,
                'service_request_type_id' => $ideaType->id,
                'type' => $ideaType->slug,
                'title' => 'Demo service request: Idea generation',
                'description' => 'Seeded multi-step idea request for manager/client lists.',
                'status' => 'in_progress',
                'priority' => 'high',
                'step_data' => [
                    "step_{$ideaStep1->id}" => [
                        'idea_title' => 'Seeded cafe loyalty app',
                        'idea_description' => 'Stamp cards digitized with push promos.',
                        'target_market' => 'Independent cafes',
                    ],
                ],
                'current_step_id' => ServiceRequestStep::where('service_request_type_id', $ideaType->id)->where('step_order', 2)->value('id'),
                'assigned_to' => $pm->id,
                'approved_at' => now()->subDays(2),
                'started_at' => now()->subDay(),
            ]);

            ServiceRequest::create([
                'user_id' => $client2->id,
                'service_request_type_id' => $ideaType->id,
                'type' => $ideaType->slug,
                'title' => 'Demo service request: Pending review',
                'description' => 'Awaiting internal review.',
                'status' => 'in_review',
                'priority' => 'medium',
                'step_data' => [
                    "step_{$ideaStep1->id}" => [
                        'idea_title' => 'Seeded fitness habit tracker',
                        'idea_description' => 'Micro-workouts tied to calendar gaps.',
                        'target_market' => 'Remote workers',
                    ],
                ],
                'current_step_id' => $ideaStep1->id,
            ]);
        }

        if ($consultType) {
            $cStep1 = ServiceRequestStep::where('service_request_type_id', $consultType->id)->where('step_order', 1)->first();
            if ($cStep1) {
                ServiceRequest::create([
                    'user_id' => $client3->id,
                    'service_request_type_id' => $consultType->id,
                    'type' => $consultType->slug,
                    'title' => 'Demo: Expert consultation intake',
                    'description' => 'Seeded consultation-type service request.',
                    'status' => 'pending',
                    'priority' => 'low',
                    'step_data' => [
                        "step_{$cStep1->id}" => [
                            'consultation_category' => 'Business Strategy',
                            'consultation_description' => 'Board deck review before Q3.',
                        ],
                    ],
                    'current_step_id' => $cStep1->id,
                ]);
            }
        }

        // --- Meetings & time slots ---
        $slotDate = now()->addDays(5)->toDateString();
        $slot = TimeSlot::create([
            'user_id' => $employee->id,
            'date' => $slotDate,
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'status' => 'available',
            'notes' => 'Demo slot',
        ]);

        $scheduledAt = Carbon::parse($slotDate.' 14:00:00');
        Meeting::create([
            'time_slot_id' => $slot->id,
            'client_id' => $client->id,
            'team_member_id' => $employee->id,
            'bookable_type' => ConsultationRequest::class,
            'bookable_id' => $consultForMeeting->id,
            'title' => 'Demo: Consultation — architecture review',
            'description' => 'Linked to a consultation request.',
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => 60,
            'status' => 'confirmed',
            'meeting_link' => 'https://example.com/meet/demo-consult',
            'confirmed_at' => now()->subDay(),
        ]);
        $slot->update(['status' => 'booked']);

        $slot2Date = now()->addDays(8)->toDateString();
        $slot2 = TimeSlot::create([
            'user_id' => $pm->id,
            'date' => $slot2Date,
            'start_time' => '09:30:00',
            'end_time' => '10:30:00',
            'status' => 'available',
        ]);
        Meeting::create([
            'time_slot_id' => $slot2->id,
            'client_id' => $client2->id,
            'team_member_id' => $pm->id,
            'title' => 'Demo: Intro call',
            'scheduled_at' => Carbon::parse($slot2Date.' 09:30:00'),
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);
        $slot2->update(['status' => 'booked']);

        $progress = app(UpdateProjectProgressAction::class);
        foreach ([$projectAlpha, $projectBeta, $projectGamma] as $p) {
            $progress->execute($p->fresh());
        }

        $this->command?->info('Demo data seeded. Extra logins: client2@vujade.com, client3@vujade.com, employee2@vujade.com (password 12345678).');
    }
}
