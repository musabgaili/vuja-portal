<?php

namespace App\Services;

use App\Models\Opportunity;
use App\Models\Project;
use App\Models\ProjectPerson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Turns a confirmed service request (idea, consultation, research, IP, copyright,
 * prototype) into a delivery Project, and records a matching won Opportunity in
 * the sales funnel so every confirmed service shows up as revenue and is linked
 * back to its project.
 *
 * The created Project carries source_type/source_id pointing at the originating
 * service, which also makes the conversion idempotent.
 */
class ServiceProjectConverter
{
    /**
     * @param  array{title:string, description?:?string, scope?:?string, client_id?:?int, budget?:?float, project_manager_id?:?int, source_label?:?string}  $data
     */
    public function convert(Model $service, array $data): Project
    {
        // Idempotent: a service is only ever converted to one project.
        $existing = Project::where('source_type', $service::class)
            ->where('source_id', $service->getKey())
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($service, $data) {
            $project = Project::create([
                'client_id' => $data['client_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'scope' => $data['scope'] ?? null,
                'source_type' => $service::class,
                'source_id' => $service->getKey(),
                'status' => 'awarded',
                'budget' => $data['budget'] ?? null,
                'project_manager_id' => $data['project_manager_id'] ?? null,
            ]);

            // Project people: client (read-only) + project manager (editor).
            if (! empty($data['client_id'])) {
                ProjectPerson::firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $data['client_id'], 'role' => 'client'],
                    ['can_edit' => false],
                );
            }
            if (! empty($data['project_manager_id'])) {
                ProjectPerson::firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $data['project_manager_id'], 'role' => 'project_manager'],
                    ['can_edit' => true],
                );
            }

            // Record the win in the sales funnel, linked to the project.
            Opportunity::create([
                'name' => $data['title'],
                'description' => $data['description'] ?? null,
                'source' => $data['source_label'] ?? 'Service request',
                'stage' => 'won',
                'expected_value' => $data['budget'] ?? 0,
                'probability' => 100,
                'won_at' => now(),
                'client_id' => $data['client_id'] ?? null,
                'owner_id' => $data['project_manager_id'] ?? null,
                'converted_project_id' => $project->id,
            ]);

            return $project;
        });
    }
}
