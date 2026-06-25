<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PayoutBankOption;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutBankOptionController extends Controller
{
    public function index()
    {
        return Inertia::render('central/bank-options/Index', [
            'banks' => PayoutBankOption::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:payout_bank_options,name'],
        ]);

        PayoutBankOption::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Bank option added successfully.');
    }

    public function update(Request $request, PayoutBankOption $bank)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:payout_bank_options,name,' . $bank->id],
            'is_active' => ['boolean'],
        ]);

        $bank->update([
            'name' => $validated['name'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return back()->with('success', 'Bank option updated successfully.');
    }
}
