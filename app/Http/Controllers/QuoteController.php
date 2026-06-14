<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    // ---- Internal ----

    public function index()
    {
        abort_unless(Auth::user()->isInternal(), 403);

        return view('crm.quotes.index', [
            'quotes' => Quote::with(['client', 'opportunity', 'creator'])->latest()->paginate(20),
        ]);
    }

    public function show(Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);

        return view('crm.quotes.show', ['quote' => $quote->load(['items', 'client', 'opportunity', 'company', 'project'])]);
    }

    public function send(Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);
        if ($quote->status === 'draft') {
            $quote->update(['status' => 'sent']);
        }

        return back()->with('success', 'Quote marked as sent to the client.');
    }

    /** Internal accept-on-behalf (e.g. verbal/phone acceptance). */
    public function acceptInternal(Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);
        $project = $this->acceptAndConvert($quote, Auth::user()->name.' (internal)', request()->ip());

        return redirect()->route('projects.manager.show', $project)
            ->with('success', 'Quote accepted — order/project created.');
    }

    public function destroy(Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);
        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Quote deleted.');
    }

    // ---- Client portal ----

    public function clientIndex()
    {
        $user = Auth::user();
        abort_unless($user->canUseClientProjectPortal(), 403);

        return view('crm.quotes.client-index', [
            'quotes' => Quote::where('client_id', $user->id)->whereIn('status', ['sent', 'accepted'])->latest()->get(),
        ]);
    }

    public function clientShow(Quote $quote)
    {
        $this->guardClient($quote);

        return view('crm.quotes.client-show', ['quote' => $quote->load('items')]);
    }

    public function clientAccept(Request $request, Quote $quote)
    {
        $this->guardClient($quote);
        if ($quote->status !== 'sent') {
            return back()->withErrors(['accept' => 'This quote is not awaiting your acceptance.']);
        }
        $validated = $request->validate(['signature' => 'required|string|max:120']);
        $this->acceptAndConvert($quote, $validated['signature'], $request->ip());

        return redirect()->route('quotes.client.show', $quote)->with('success', 'Quote accepted — thank you! Your project is now underway.');
    }

    public function clientReject(Request $request, Quote $quote)
    {
        $this->guardClient($quote);
        $validated = $request->validate(['reject_reason' => 'nullable|string|max:255']);
        $quote->update(['status' => 'rejected', 'reject_reason' => $validated['reject_reason'] ?? null]);

        return back()->with('success', 'Quote declined.');
    }

    private function guardClient(Quote $quote): void
    {
        $user = Auth::user();
        abort_unless($user->canUseClientProjectPortal() && $quote->client_id === $user->id, 403);
    }

    /** Accepting a quote creates the order (a Project) and wins any linked opportunity. */
    private function acceptAndConvert(Quote $quote, ?string $signature, ?string $ip): Project
    {
        if ($quote->isAccepted() && $quote->project) {
            return $quote->project;
        }

        return DB::transaction(function () use ($quote, $signature, $ip) {
            $project = Project::create([
                'title' => $quote->title,
                'description' => $quote->scope ? \Illuminate\Support\Str::limit(strip_tags($quote->scope), 4000) : $quote->title,
                'client_id' => $quote->client_id,
                'budget' => $quote->total_client,
                'status' => 'awarded',
                'project_manager_id' => $quote->opportunity?->owner_id ?? $quote->created_by,
                'source_type' => Quote::class,
                'source_id' => $quote->id,
            ]);

            $quote->update([
                'status' => 'accepted',
                'accepted_signature' => $signature,
                'accepted_at' => now(),
                'accepted_ip' => $ip,
                'project_id' => $project->id,
            ]);

            if ($quote->opportunity && $quote->opportunity->isOpen()) {
                $quote->opportunity->update([
                    'stage' => 'won', 'won_at' => now(), 'probability' => 100, 'converted_project_id' => $project->id,
                ]);
            }

            return $project;
        });
    }
}
