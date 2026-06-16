<?php

namespace App\Services\Engagement;

use App\Models\PointGrantRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Discretionary grants (spec §13): a Project Manager suggests; only a Manager
 * may approve. A PM alone grants nothing, and no one may approve their own.
 */
class GrantApprovalService
{
    public function __construct(private PointsService $points) {}

    public function suggest(User $client, int $points, string $reason, User $suggestedBy): PointGrantRequest
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Grant points must be positive.');
        }

        return PointGrantRequest::create([
            'client_id' => $client->id,
            'points' => $points,
            'reason' => $reason,
            'status' => 'suggested',
            'suggested_by' => $suggestedBy->id,
        ]);
    }

    public function approve(PointGrantRequest $request, User $manager): PointGrantRequest
    {
        if (! $request->isSuggested()) {
            return $request;
        }
        // Two-person integrity: cannot approve your own suggestion.
        if ($request->suggested_by === $manager->id) {
            throw new \RuntimeException('You cannot approve your own grant suggestion.');
        }

        return DB::transaction(function () use ($request, $manager) {
            $client = User::findOrFail($request->client_id);
            $account = $this->points->accountFor($client);

            $txn = $this->points->awardPoints(
                $account,
                (int) $request->points,
                'manual_grant',
                $request,
                $request->reason,
                false,
                $manager->id,
            );

            $request->update([
                'status' => 'approved',
                'approved_by' => $manager->id,
                'points_transaction_id' => $txn->id,
            ]);

            return $request;
        });
    }

    public function reject(PointGrantRequest $request, User $manager): PointGrantRequest
    {
        if (! $request->isSuggested()) {
            return $request;
        }
        $request->update(['status' => 'rejected', 'approved_by' => $manager->id]);

        return $request;
    }
}
