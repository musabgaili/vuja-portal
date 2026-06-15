<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'opportunity_id', 'company_id', 'contact_id', 'client_id', 'created_by', 'project_id',
        'approved_by', 'customer_category',
        'title', 'scope', 'status', 'total_internal', 'total_client', 'valid_until',
        'accepted_signature', 'accepted_at', 'accepted_ip', 'approved_at', 'reject_reason',
        // Scope Planner upgrade
        'quote_number', 'language', 'length', 'structure', 'subject', 'beneficiary', 'client_ref',
        'brief', 'ai_content', 'vat_rate', 'components_internal_total', 'components_client_total',
        'subtotal', 'vat_amount', 'grand_total', 'validity_days',
        'payment_status', 'paid_at',
    ];

    protected $casts = [
        'total_internal' => 'decimal:2',
        'total_client' => 'decimal:2',
        'valid_until' => 'date',
        'accepted_at' => 'datetime',
        'approved_at' => 'datetime',
        // Scope Planner upgrade
        'ai_content' => 'array',
        'vat_rate' => 'decimal:2',
        'components_internal_total' => 'decimal:2',
        'components_client_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'validity_days' => 'integer',
        'paid_at' => 'datetime',
    ];

    /** The invoice/headline amount: VAT-inclusive grand total if priced, else the legacy client total. */
    public function invoiceTotal(): float
    {
        return (float) ($this->grand_total > 0 ? $this->grand_total : $this->total_client);
    }

    /** An accepted quote is the client's invoice. */
    public function isInvoice(): bool
    {
        return $this->status === 'accepted';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(QuoteComment::class)->latest();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(QuoteScope::class)->orderBy('sort_order');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(QuoteMilestone::class)->orderBy('sort_order');
    }

    /** The client-facing components figure: employee override, else itemized sum. */
    public function componentsClientTotal(): float
    {
        return (float) ($this->components_client_total ?? $this->components_internal_total);
    }

    /** Derived recipient name/email for documents (client user, else company/contact). */
    public function getClientNameAttribute(): ?string
    {
        return $this->client?->name ?? $this->company?->name ?? $this->contact?->name;
    }

    public function getClientEmailAttribute(): ?string
    {
        return $this->client?->email ?? $this->contact?->email;
    }

    /**
     * Client-facing line items for the document: every inventory COMPONENT row
     * collapsed into one "Electronic Components" line (margin hidden), with
     * service / other rows kept itemised. Plain arrays for the renderer.
     */
    public function clientVisibleItems(): \Illuminate\Support\Collection
    {
        $rows = collect();
        $componentsTotal = $this->componentsClientTotal();

        if ($this->items->where('type', 'component')->isNotEmpty() || $componentsTotal > 0) {
            $rows->push((object) [
                'description' => __('scope.electronic_components'),
                'unit' => '—',
                'quantity' => 1,
                'unit_price' => $componentsTotal,
                'line_total' => $componentsTotal,
            ]);
        }

        foreach ($this->items->whereIn('type', ['service', 'other'])->sortBy('sort_order') as $it) {
            // Recover the unit price from the line total when it wasn't stored,
            // so the document never shows "lineTotal × qty = lineTotal".
            $unit = (float) ($it->unit_price ?: ($it->line_client / max(1, (int) $it->qty)));
            $rows->push((object) [
                'description' => $it->name,
                'unit' => $it->unit ?: '—',
                'quantity' => (float) $it->qty,
                'unit_price' => $unit,
                'line_total' => (float) ($it->line_client ?: $unit * $it->qty),
            ]);
        }

        return $rows;
    }

    /** All line items itemised (internal / employee view). */
    public function internalItems(): \Illuminate\Support\Collection
    {
        return $this->items->sortBy('sort_order')->values();
    }

    /** Client-facing pricing: grouped by category (item prices hidden). Legacy view. */
    public function clientGrouped(): array
    {
        return $this->items->groupBy('category')
            ->map(fn ($g) => (float) $g->sum('line_client'))
            ->toArray();
    }

    public function margin(): float
    {
        return (float) $this->total_client - (float) $this->total_internal;
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /** Statuses where the creator may still edit / re-submit the quote. */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'changes_requested'], true);
    }

    /** Has cleared internal approval and is ready to send / been sent / accepted. */
    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'sent', 'accepted'], true);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'accepted', 'approved' => 'success',
            'sent' => 'info',
            'pending_approval' => 'warning',
            'changes_requested' => 'warning',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function statusLabel(): string
    {
        return __('portal.quote.status.'.$this->status);
    }
}
