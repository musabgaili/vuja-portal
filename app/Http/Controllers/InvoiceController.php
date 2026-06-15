<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
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

        return view('invoices.create', [
            'clients' => User::where('role', 'client')->orderBy('name')->get(),
            'projects' => Project::orderBy('title')->get(),
            'quote' => $quote,
        ]);
    }

    public function store(Request $request)
    {
        $this->guardManage();

        $data = $request->validate([
            'client_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'quote_id' => 'nullable|exists:quotes,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'invoice_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $path = null;
        if ($request->hasFile('invoice_file')) {
            $path = $request->file('invoice_file')->store('invoices', 'private');
        }

        $invoice = Invoice::create([
            'invoice_number' => $this->nextInvoiceNumber(),
            'client_id' => $data['client_id'],
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

        return redirect()->route('invoices.show', $invoice)->with('success', __('portal.invoices.created'));
    }

    public function show(Invoice $invoice)
    {
        $this->guardManage();

        return view('invoices.show', ['invoice' => $invoice->load(['client', 'project', 'quote', 'creator'])]);
    }

    /** Confirm payment after reviewing the client's receipt. */
    public function markPaid(Invoice $invoice)
    {
        $this->guardManage();

        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

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

        return back()->with('success', __('portal.invoices.reopened'));
    }

    public function cancel(Invoice $invoice)
    {
        $this->guardManage();

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', __('portal.invoices.cancelled_done'));
    }

    // ===================================================================
    // Client
    // ===================================================================

    public function clientIndex()
    {
        $user = Auth::user();
        abort_unless($user->canUseClientProjectPortal(), 403);

        $invoices = Invoice::where('client_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->with('project')->latest()->get();

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
        abort_unless(Auth::id() === $invoice->client_id, 403);
    }

    /** The owning client OR an internal manager/PM may view/download files. */
    private function guardViewer(Invoice $invoice): void
    {
        $user = Auth::user();
        $isManage = $user && $user->isInternal() && ($user->isManager() || $user->isProjectManager());
        abort_unless($isManage || Auth::id() === $invoice->client_id, 403);
    }
}
