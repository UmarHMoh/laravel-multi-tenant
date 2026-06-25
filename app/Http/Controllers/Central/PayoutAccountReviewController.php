<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\TenantPayoutAccount;
use Illuminate\Http\Request;

class PayoutAccountReviewController extends Controller
{
    public function approve(Request $request, TenantPayoutAccount $account)
    {
        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $account->update([
            'status' => 'verified',
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => 'central_admin',
        ]);

        return back()->with('success', 'Payout account approved successfully.');
    }

    public function reject(Request $request, TenantPayoutAccount $account)
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:2000'],
        ]);

        $account->update([
            'status' => 'rejected',
            'review_notes' => $validated['review_notes'],
            'reviewed_at' => now(),
            'reviewed_by' => 'central_admin',
        ]);

        return back()->with('success', 'Payout account rejected.');
    }

    public function markPending(Request $request, TenantPayoutAccount $account)
    {
        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $account->update([
            'status' => 'pending',
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => 'central_admin',
        ]);

        return back()->with('success', 'Payout account marked as pending.');
    }
}
