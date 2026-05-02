<?php

namespace App\Console\Commands;

use App\Actions\Projects\UpdateProjectProgressAction;
use App\Models\Project;
use Illuminate\Console\Command;

class RecalculateProjectProgress extends Command
{
    protected $signature = 'projects:recalculate-progress';

    protected $description = 'Recalculate progress for all projects based on tasks and milestones';

    public function handle()
    {
        $this->info('Recalculating progress for all projects...');

        $projects = Project::all();
        $progressAction = app(UpdateProjectProgressAction::class);

        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        foreach ($projects as $project) {
            $progressAction->execute($project);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("✅ Successfully recalculated progress for {$projects->count()} projects!");

        return Command::SUCCESS;
    }
}
