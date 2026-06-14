<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    private function guard(): void
    {
        abort_unless(Auth::user()->isInternal(), 403);
    }

    public function index(Request $request)
    {
        $this->guard();
        $q = Contact::with(['company', 'tags'])->latest();
        if ($s = trim((string) $request->input('search'))) {
            $q->where(fn ($b) => $b->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
        }

        return view('crm.contacts.index', ['contacts' => $q->paginate(20)->withQueryString()]);
    }

    public function create(Request $request)
    {
        $this->guard();

        return view('crm.contacts.create', $this->formData() + ['preCompany' => $request->integer('company_id')]);
    }

    public function store(Request $request)
    {
        $this->guard();
        $contact = Contact::create($this->validateData($request));
        $contact->syncTagsFromString($request->input('tags'));

        return redirect()->route('contacts.index')->with('success', 'Contact created.');
    }

    public function edit(Contact $contact)
    {
        $this->guard();

        return view('crm.contacts.edit', $this->formData() + ['contact' => $contact->load('tags')]);
    }

    public function update(Request $request, Contact $contact)
    {
        $this->guard();
        $contact->update($this->validateData($request));
        $contact->syncTagsFromString($request->input('tags'));

        return redirect()->route('contacts.index')->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact)
    {
        $this->guard();
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted.');
    }

    private function formData(): array
    {
        return [
            'companies' => Company::orderBy('name')->get(),
            'owners' => User::where('type', 'internal')->orderBy('name')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:160',
            'company_id' => 'nullable|exists:companies,id',
            'job_title' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40',
            'is_primary' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);
    }
}
