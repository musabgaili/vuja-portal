<?php

namespace App\Targets\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/** Binds to the existing projects (won quotes) + their BoQ. Spec §5, §11. */
interface ProjectsBridge
{
    /**
     * Service-line revenue recognized in the month (on project completion),
     * with the assigned engineer + category.
     * @return Collection<int, object> {team_member_id, activity_category_id, category_key, amount, project_id}
     */
    public function recognizedRevenue(Carbon $monthStart): Collection;

    /**
     * Service lines (from the won quote's BoQ) available to assign to an engineer
     * — excludes the Electronic Components line (materials, not engineer revenue).
     * @return Collection<int, object> {line_id, name, category, amount}
     */
    public function assignableLines(int $projectId): Collection;
}
