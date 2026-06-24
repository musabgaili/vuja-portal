<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An employee/PM/manager request to spend money — either a reimbursement (already
 * paid out of pocket) or a purchase (asking the company to buy something). Scoped
 * to a project (rolls into its budget) or general/company spend. Routed for
 * approval by role with a strict no-self-approval rule.
 */
class SpendRequest extends Model
{
    protected $fillable = [
        'requester_id', 'scope', 'project_id', 'type', 'title', 'description', 'category',
        'amount', 'currency', 'product_url', 'quantity', 'receipt_file', 'status',
        'reviewed_by', 'reviewed_at', 'review_notes', 'actual_amount', 'actual_receipt_file',
        'completed_at', 'self_recorded', 'project_expense_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'quantity' => 'integer',
        'self_recorded' => 'boolean',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function projectExpense(): BelongsTo
    {
        return $this->belongsTo(ProjectExpense::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SpendRequestItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SpendRequestFile::class);
    }

    /** Receipts/attachments attached at request time. */
    public function receipts(): HasMany
    {
        return $this->files()->whereIn('kind', ['receipt', 'attachment']);
    }

    /** Proof-of-purchase files attached at fulfilment time. */
    public function actualReceipts(): HasMany
    {
        return $this->files()->where('kind', 'actual_receipt');
    }

    /** Sum of the line items (the request's estimated/claimed total). */
    public function itemsTotal(): float
    {
        return round((float) $this->items->sum(fn (SpendRequestItem $i) => $i->lineTotal()), 2);
    }

    /** All buy links across the line items. */
    public function links(): array
    {
        return $this->items->pluck('product_url')->filter()->values()->all();
    }

    /** First receipt path (back-compat helper for the ledger). */
    public function firstReceiptPath(): ?string
    {
        return $this->receipts->first()->path ?? $this->receipt_file;
    }

    // ---- State helpers ----
    public function isProject(): bool { return $this->scope === 'project'; }
    public function isGeneral(): bool { return $this->scope === 'general'; }
    public function isPurchase(): bool { return $this->type === 'purchase'; }
    public function isReimbursement(): bool { return $this->type === 'reimbursement'; }
    public function isPending(): bool { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    /** Approved purchase, not yet bought = a committed (not-yet-spent) amount. */
    public function isCommitted(): bool
    {
        return $this->isPurchase() && $this->isApproved();
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'completed' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function statusLabel(): string
    {
        return __('portal.spend.status.'.$this->status);
    }

    public function effectiveAmount(): float
    {
        return (float) ($this->actual_amount ?? $this->amount);
    }

    /**
     * Role-routed approval with strict segregation of duties:
     *  - never your own request;
     *  - managers approve anything (incl. another manager's — Option A peer review);
     *  - a project manager approves only an EMPLOYEE's project request on a project
     *    they manage. Their own spend (and all general spend) escalates to a manager.
     */
    public function isApprovableBy(User $user): bool
    {
        if (! $user->isInternal() || $user->id === $this->requester_id || ! $this->isPending()) {
            return false;
        }

        if ($user->isManager()) {
            return true;
        }

        if ($user->isProjectManager()) {
            $requester = $this->relationLoaded('requester') ? $this->requester : $this->requester()->first();

            return $this->isProject()
                && $requester && $requester->isEmployee()
                && $this->project_id
                && (int) optional($this->project)->project_manager_id === (int) $user->id;
        }

        return false;
    }
}
