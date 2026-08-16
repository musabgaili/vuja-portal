<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentRequest;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Customer invoices. PMs/managers raise invoices for clients (optionally
 * attaching the document); clients upload a payment receipt as proof, which
 * moves the invoice from "unpaid" to "proof submitted" — then a manager/PM
 * confirms it paid after reviewing the receipt. Files live on the private disk
 * and are streamed only to the owner client or internal staff.
 */
class InvoiceController extends Controller
{
    // ===================================================================
    // Internal (PM + manager)
    // ===================================================================

    public function index(Request $request)
    {
        $this->guardManage();

        $query = Invoice::with(['client', 'project'])->latest();
        if ($request->filled('status') && in_array($request->input('status'), ['unpaid', 'proof_submitted', 'paid', 'cancelled'], true)) {
            $query->where('status', $request->input('status'));
        }

        return view('invoices.index', [
            'invoices' => $query->paginate(20),
            'outstanding' => (float) Invoice::whereIn('status', ['unpaid', 'proof_submitted'])->sum('amount'),
        ]);
    }

    public function create(Request $request)
    {
        $this->guardManage();

        $quote = $request->filled('quote') ? Quote::find($request->input('quote')) : null;
        $clients = User::where('role', 'client')->orderBy('name')->get(['id', 'name', 'email']);

        $paymentRequests = PaymentRequest::query()
            ->whereNull('payable_id')
            ->latest()
            ->limit(200)
            ->get(['id', 'uuid', 'name', 'email', 'title', 'title_en', 'title_ar', 'status', 'total_amount_minor', 'currency', 'paid_at', 'created_at'])
            ->map(fn (PaymentRequest $p) => [
                'id' => $p->id,
                'email' => mb_strtolower((string) $p->email),
                'title' => $p->localizedTitle('en'),
                'status' => $p->status,
                'amount' => number_format($p->total_amount_minor / 100, 2, '.', ''),
                'currency' => $p->currency,
                'paid_at' => optional($p->paid_at)?->toDateString(),
                'created_at' => optional($p->created_at)?->toDateString(),
            ])
            ->values();

        return view('invoices.create', [
            'clients' => $clients,
            'projects' => Project::orderBy('title')->get(),
            'quote' => $quote,
            'paymentRequestsJson' => $paymentRequests,
        ]);
    }

    public function store(Request $request)
    {
        $this->guardManage();

        $data = $request->validate([
            'client_id' => 'nullable|exists:users,id',
            'recipient_name' => 'required|string|max:160',
            'recipient_email' => 'required|email:filter|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'quote_id' => 'nullable|exists:quotes,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'invoice_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'payment_request_ids' => 'nullable|array',
            'payment_request_ids.*' => 'integer|exists:payment_requests,id',
        ]);

        $email = mb_strtolower(trim($data['recipient_email']));
        $name = trim($data['recipient_name']);
        $clientId = filled($data['client_id'] ?? null) ? (int) $data['client_id'] : null;

        // If no client chosen, attach a matching registered client by email when one exists.
        if (! $clientId) {
            $clientId = User::query()
                ->where('role', 'client')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->value('id');
        } else {
            $client = User::query()->find($clientId);
            if ($client && mb_strtolower((string) $client->email) !== $email) {
                return back()
                    ->withInput()
                    ->withErrors(['recipient_email' => __('portal.invoices.email_client_mismatch')]);
            }
        }

        $paymentIds = collect($data['payment_request_ids'] ?? [])->unique()->values();
        $payments = PaymentRequest::query()
            ->whereIn('id', $paymentIds)
            ->whereNull('payable_id')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->get();

        if ($paymentIds->isNotEmpty() && $payments->count() !== $paymentIds->count()) {
            return back()
                ->withInput()
                ->withErrors(['payment_request_ids' => __('portal.invoices.payments_invalid')]);
        }

        $path = null;
        if ($request->hasFile('invoice_file')) {
            $path = $request->file('invoice_file')->store('invoices', 'private');
        }

        $invoice = DB::transaction(function () use ($data, $path, $payments, $clientId, $name, $email) {
            $invoice = Invoice::create([
                'invoice_number' => $this->nextInvoiceNumber(),
                'client_id' => $clientId,
                'recipient_name' => $name,
                'recipient_email' => $email,
                'project_id' => $data['project_id'] ?? null,
                'quote_id' => $data['quote_id'] ?? null,
                'created_by' => Auth::id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'amount' => $data['amount'],
                'currency' => config('scope.currency', 'SAR'),
                'status' => 'unpaid',
                'due_date' => $data['due_date'] ?? null,
                'invoice_file' => $path,
            ]);

            foreach ($payments as $payment) {
                $payment->payable()->associate($invoice);
                $payment->save();
            }

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', __('portal.invoices.created'));
    }

    public function show(Invoice $invoice)
    {
        $this->guardManage();

        return view('invoices.show', [
            'invoice' => $invoice->load(['client', 'project', 'quote', 'creator', 'paymentRequests']),
        ]);
    }

    /** Confirm payment after reviewing the client's receipt. */
    public function markPaid(Invoice $invoice)
    {
        $this->guardManage();

        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        // Vest any referral payment reward once the client's project is paid in full.
        if ($invoice->project_id) {
            app(\App\Services\Engagement\EarningEngine::class)->recordPaidInFull((int) $invoice->project_id);
        }

        return back()->with('success', __('portal.invoices.marked_paid'));
    }

    /** Send an invoice back to unpaid (e.g. receipt rejected). */
    public function reopen(Request $request, Invoice $invoice)
    {
        $this->guardManage();
        $validated = $request->validate(['note' => 'nullable|string|max:1000']);

        $invoice->update([
            'status' => 'unpaid',
            'paid_at' => null,
            'note' => $validated['note'] ?? $invoice->note,
        ]);

        // If this un-pays the project, claw back any referral reward that vested on it.
        if ($invoice->project_id) {
            app(\App\Services\Engagement\EarningEngine::class)->recordPaymentReopened((int) $invoice->project_id);
        }

        return back()->with('success', __('portal.invoices.reopened'));
    }

    public function cancel(Invoice $invoice)
    {
        $this->guardManage();
        $wasPaid = $invoice->status === 'paid';

        $invoice->update(['status' => 'cancelled']);

        if ($wasPaid && $invoice->project_id) {
            app(\App\Services\Engagement\EarningEngine::class)->recordPaymentReopened((int) $invoice->project_id);
        }

        return back()->with('success', __('portal.invoices.cancelled_done'));
    }

    // ===================================================================
    // Client
    // ===================================================================

    public function clientIndex()
    {
        $user = Auth::user();
        abort_unless($user->canUseClientProjectPortal(), 403);

        $email = mb_strtolower((string) $user->email);
        $invoices = Invoice::query()
            ->where(function ($query) use ($user, $email) {
                $query->where('client_id', $user->id)
                    ->orWhereRaw('LOWER(recipient_email) = ?', [$email]);
            })
            ->where('status', '!=', 'cancelled')
            ->with('project')
            ->latest()
            ->get();

        $outstanding = (float) $invoices->whereIn('status', ['unpaid', 'proof_submitted'])->sum('amount');

        return view('crm.quotes.client-invoices', compact('invoices', 'outstanding'));
    }

    /** Client uploads a payment receipt → invoice moves to "proof submitted". */
    public function uploadReceipt(Request $request, Invoice $invoice)
    {
        $this->guardClientOwner($invoice);
        abort_unless($invoice->awaitingPayment(), 403);

        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Replace any prior receipt.
        if ($invoice->receipt_path && Storage::disk('private')->exists($invoice->receipt_path)) {
            Storage::disk('private')->delete($invoice->receipt_path);
        }

        $invoice->update([
            'receipt_path' => $request->file('receipt')->store('invoice-receipts', 'private'),
            'receipt_uploaded_at' => now(),
            'status' => 'proof_submitted',
        ]);

        return back()->with('success', __('portal.invoices.receipt_uploaded'));
    }

    public function downloadFile(Invoice $invoice)
    {
        $this->guardViewer($invoice);
        abort_unless($invoice->invoice_file && Storage::disk('private')->exists($invoice->invoice_file), 404);

        return Storage::disk('private')->download($invoice->invoice_file, $invoice->invoice_number.'.'.pathinfo($invoice->invoice_file, PATHINFO_EXTENSION));
    }

    public function downloadReceipt(Invoice $invoice)
    {
        $this->guardViewer($invoice);
        abort_unless($invoice->receipt_path && Storage::disk('private')->exists($invoice->receipt_path), 404);

        return Storage::disk('private')->download($invoice->receipt_path, 'receipt-'.$invoice->invoice_number.'.'.pathinfo($invoice->receipt_path, PATHINFO_EXTENSION));
    }

    // ===================================================================
    // Helpers
    // ===================================================================

    private function nextInvoiceNumber(): string
    {
        $seq = DB::transaction(function () {
            $row = DB::table('scope_counters')->where('key', 'invoice')->lockForUpdate()->first();
            if (! $row) {
                DB::table('scope_counters')->insert(['key' => 'invoice', 'value' => 0, 'created_at' => now(), 'updated_at' => now()]);
                $current = 0;
            } else {
                $current = (int) $row->value;
            }
            DB::table('scope_counters')->where('key', 'invoice')->update(['value' => $current + 1, 'updated_at' => now()]);

            return $current + 1;
        });

        return 'INV-'.Carbon::now()->format('Y').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /** PMs and managers may create/manage invoices. */
    private function guardManage(): void
    {
        $user = Auth::user();
        abort_unless($user && $user->isInternal() && ($user->isManager() || $user->isProjectManager()), 403);
    }

    /** Only the owning client may act on (upload to) their invoice. */
    private function guardClientOwner(Invoice $invoice): void
    {
        abort_unless($this->userOwnsInvoice(Auth::user(), $invoice), 403);
    }

    /** The owning client OR an internal manager/PM may view/download files. */
    private function guardViewer(Invoice $invoice): void
    {
        $user = Auth::user();
        $isManage = $user && $user->isInternal() && ($user->isManager() || $user->isProjectManager());
        abort_unless($isManage || $this->userOwnsInvoice($user, $invoice), 403);
    }

    private function userOwnsInvoice(?User $user, Invoice $invoice): bool
    {
        if (! $user) {
            return false;
        }

        if ((int) $invoice->client_id === (int) $user->id) {
            return true;
        }

        $email = mb_strtolower((string) $user->email);

        return filled($invoice->recipient_email)
            && mb_strtolower((string) $invoice->recipient_email) === $email;
    }
}
