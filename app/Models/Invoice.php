<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A customer invoice raised by a manager/PM. The client uploads a payment
 * receipt (proof); a manager confirms it paid. See InvoiceController.
 */
class Invoice extends Model
{
    use HasFactory, HasUuidRouteKey;

    protected $fillable = [
        'uuid', 'invoice_number', 'client_id', 'recipient_name', 'recipient_email',
        'project_id', 'quote_id', 'created_by',
        'title', 'description', 'amount', 'currency', 'status', 'due_date',
        'invoice_file', 'receipt_path', 'receipt_uploaded_at', 'paid_at', 'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'receipt_uploaded_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentRequests(): MorphMany
    {
        return $this->morphMany(PaymentRequest::class, 'payable');
    }

    public function recipientName(): string
    {
        return (string) ($this->recipient_name ?: $this->client?->name ?: '—');
    }

    public function recipientEmail(): ?string
    {
        $email = $this->recipient_email ?: $this->client?->email;

        return filled($email) ? mb_strtolower((string) $email) : null;
    }

    public function isGuest(): bool
    {
        return blank($this->client_id);
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function isProofSubmitted(): bool
    {
        return $this->status === 'proof_submitted';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function hasReceipt(): bool
    {
        return filled($this->receipt_path);
    }

    public function hasInvoiceFile(): bool
    {
        return filled($this->invoice_file);
    }

    /** Whether the client may still upload / replace a payment receipt. */
    public function awaitingPayment(): bool
    {
        return in_array($this->status, ['unpaid', 'proof_submitted'], true);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'paid' => 'success',
            'proof_submitted' => 'info',
            'cancelled' => 'secondary',
            default => 'warning', // unpaid
        };
    }

    public function statusLabel(): string
    {
        return __('portal.invoices.status.'.$this->status);
    }
}
