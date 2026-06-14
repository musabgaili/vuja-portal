<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    private function guard(): void
    {
        abort_unless(Auth::user()->isInternal(), 403);
    }

    public function index(Request $request)
    {
        $this->guard();
        $q = Company::withCount(['contacts', 'opportunities'])->with('tags')->latest();
        if ($s = trim((string) $request->input('search'))) {
            $q->where(fn ($b) => $b->where('name', 'like', "%{$s}%")->orWhere('industry', 'like', "%{$s}%"));
        }

        return view('crm.companies.index', ['companies' => $q->paginate(20)->withQueryString()]);
    }

    public function create()
    {
        $this->guard();

        return view('crm.companies.create', ['owners' => $this->owners()]);
    }

    public function store(Request $request)
    {
        $this->guard();
        $company = Company::create($this->validateData($request));
        $company->syncTagsFromString($request->input('tags'));

        return redirect()->route('companies.show', $company)->with('success', 'Company created.');
    }

    public function show(Company $company)
    {
        $this->guard();
        $company->load(['contacts', 'opportunities.owner', 'tags', 'owner']);

        return view('crm.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        $this->guard();

        return view('crm.companies.edit', ['company' => $company->load('tags'), 'owners' => $this->owners()]);
    }

    public function update(Request $request, Company $company)
    {
        $this->guard();
        $company->update($this->validateData($request));
        $company->syncTagsFromString($request->input('tags'));

        return redirect()->route('companies.show', $company)->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        $this->guard();
        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company deleted.');
    }

    private function owners()
    {
        return User::where('type', 'internal')->orderBy('name')->get();
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:200',
            'industry' => 'nullable|string|max:120',
            'website' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);
    }
}
