<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceRequestType;
use App\Models\ServiceRequestStep;
use App\Models\StepFormField;
use App\Models\User;

class ServiceRequestTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = User::where('role', 'manager')->first();
        
        if (!$manager) {
            $this->command->error('No manager user found. Please run UserSeeder first.');
            return;
        }

        // Create Idea Generation Service Type
        $ideaType = ServiceRequestType::create([
            'name' => 'Idea Generation',
            'slug' => 'idea-generation',
            'description' => 'Transform your innovative ideas into actionable business concepts with our AI-powered ideation tools.',
            'icon' => 'fas fa-lightbulb',
            'color' => '#f59e0b',
            'is_active' => true,
            'sort_order' => 1,
            'created_by' => $manager->id,
        ]);

        // Create steps for Idea Generation
        $step1 = ServiceRequestStep::create([
            'service_request_type_id' => $ideaType->id,
            'name' => 'Initial Idea Submission',
            'description' => 'Describe your initial idea and requirements',
            'step_order' => 1,
            'step_type' => 'form',
            'is_required' => true,
            'is_active' => true,
        ]);

        // Add form fields for step 1
        StepFormField::create([
            'service_request_step_id' => $step1->id,
            'field_name' => 'idea_title',
            'field_label' => 'Idea Title',
            'field_type' => 'text',
            'field_description' => 'Give your idea a catchy title',
            'is_required' => true,
            'field_order' => 1,
        ]);

        StepFormField::create([
            'service_request_step_id' => $step1->id,
            'field_name' => 'idea_description',
            'field_label' => 'Idea Description',
            'field_type' => 'textarea',
            'field_description' => 'Describe your idea in detail',
            'is_required' => true,
            'field_order' => 2,
        ]);

        StepFormField::create([
            'service_request_step_id' => $step1->id,
            'field_name' => 'target_market',
            'field_label' => 'Target Market',
            'field_type' => 'text',
            'field_description' => 'Who is your target audience?',
            'is_required' => false,
            'field_order' => 3,
        ]);

        $step2 = ServiceRequestStep::create([
            'service_request_type_id' => $ideaType->id,
            'name' => 'AI Assessment',
            'description' => 'Enhance your idea using AI tools',
            'step_order' => 2,
            'step_type' => 'external_api',
            'step_config' => [
                'api_type' => 'ai_assessment',
                'requires_tokens' => true,
                'token_cost' => 10,
            ],
            'is_required' => false,
            'is_active' => true,
        ]);

        $step3 = ServiceRequestStep::create([
            'service_request_type_id' => $ideaType->id,
            'name' => 'Price Negotiation',
            'description' => 'Discuss pricing and terms with our team',
            'step_order' => 3,
            'step_type' => 'approval',
            'step_config' => [
                'requires_manager_approval' => true,
                'allows_negotiation' => true,
            ],
            'is_required' => true,
            'is_active' => true,
        ]);

        // Create Consultation Service Type
        $consultationType = ServiceRequestType::create([
            'name' => 'Expert Consultation',
            'slug' => 'expert-consultation',
            'description' => 'Get expert advice and guidance from our experienced professionals.',
            'icon' => 'fas fa-comments',
            'color' => '#10b981',
            'is_active' => true,
            'sort_order' => 2,
            'created_by' => $manager->id,
        ]);

        // Create steps for Consultation
        $consultStep1 = ServiceRequestStep::create([
            'service_request_type_id' => $consultationType->id,
            'name' => 'Consultation Request',
            'description' => 'Tell us about your consultation needs',
            'step_order' => 1,
            'step_type' => 'form',
            'is_required' => true,
            'is_active' => true,
        ]);

        StepFormField::create([
            'service_request_step_id' => $consultStep1->id,
            'field_name' => 'consultation_category',
            'field_label' => 'Consultation Category',
            'field_type' => 'select',
            'field_description' => 'Select the type of consultation you need',
            'field_options' => [
                'Business Strategy',
                'Technology Consulting',
                'Marketing & Branding',
                'Legal Advice',
                'Financial Planning',
                'Other'
            ],
            'is_required' => true,
            'field_order' => 1,
        ]);

        StepFormField::create([
            'service_request_step_id' => $consultStep1->id,
            'field_name' => 'consultation_description',
            'field_label' => 'Consultation Description',
            'field_type' => 'textarea',
            'field_description' => 'Describe what you need help with',
            'is_required' => true,
            'field_order' => 2,
        ]);

        $consultStep2 = ServiceRequestStep::create([
            'service_request_type_id' => $consultationType->id,
            'name' => 'Meeting Scheduling',
            'description' => 'Schedule your consultation meeting',
            'step_order' => 2,
            'step_type' => 'external_api',
            'step_config' => [
                'api_type' => 'calendar_integration',
                'requires_employee_confirmation' => true,
            ],
            'is_required' => true,
            'is_active' => true,
        ]);

        // Create Research & IP Service Type
        $researchType = ServiceRequestType::create([
            'name' => 'Research & IP',
            'slug' => 'research-ip',
            'description' => 'Comprehensive research and intellectual property services.',
            'icon' => 'fas fa-search',
            'color' => '#3b82f6',
            'is_active' => true,
            'sort_order' => 3,
            'created_by' => $manager->id,
        ]);

        // Create steps for Research & IP
        $researchStep1 = ServiceRequestStep::create([
            'service_request_type_id' => $researchType->id,
            'name' => 'NDA & SLA Signing',
            'description' => 'Sign required legal documents',
            'step_order' => 1,
            'step_type' => 'external_api',
            'step_config' => [
                'api_type' => 'digital_signature',
                'required_documents' => ['NDA', 'SLA'],
            ],
            'is_required' => true,
            'is_active' => true,
        ]);

        $researchStep2 = ServiceRequestStep::create([
            'service_request_type_id' => $researchType->id,
            'name' => 'Research Requirements',
            'description' => 'Provide research details and requirements',
            'step_order' => 2,
            'step_type' => 'form',
            'is_required' => true,
            'is_active' => true,
        ]);

        StepFormField::create([
            'service_request_step_id' => $researchStep2->id,
            'field_name' => 'research_topic',
            'field_label' => 'Research Topic',
            'field_type' => 'text',
            'field_description' => 'What do you need researched?',
            'is_required' => true,
            'field_order' => 1,
        ]);

        StepFormField::create([
            'service_request_step_id' => $researchStep2->id,
            'field_name' => 'research_links',
            'field_label' => 'Relevant Links',
            'field_type' => 'textarea',
            'field_description' => 'Provide any relevant links or references',
            'is_required' => false,
            'field_order' => 2,
        ]);

        StepFormField::create([
            'service_request_step_id' => $researchStep2->id,
            'field_name' => 'research_files',
            'field_label' => 'Upload Files',
            'field_type' => 'file',
            'field_description' => 'Upload any relevant documents or files',
            'is_required' => false,
            'field_order' => 3,
        ]);

        $this->command->info('Service request types and steps created successfully!');
    }
}