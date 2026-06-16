<?php

namespace App\Engagement\Contracts;

/** Binds to the existing projects / invoices. Spec §5. */
interface BillingBridge
{
    /** The project's value in SAR (quoted/awarded budget, else sum of paid invoices). */
    public function projectValue(int $projectId): float;

    /** True when the project has at least one paid invoice and none still awaiting payment. */
    public function isProjectPaidInFull(int $projectId): bool;

    /** The client (user id) the project belongs to. */
    public function clientOf(int $projectId): ?int;

    /** The client's first fully-paid project id, if any (referral payment trigger). */
    public function firstPaidProjectForClient(int $clientId): ?int;
}
