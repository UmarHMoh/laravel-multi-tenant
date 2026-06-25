<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\PayoutBankOption;
use App\Models\TenantPayoutAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class PayoutAccountController extends Controller
{
    public function index()
    {
        $tenantId = tenant('id');

        $account = TenantPayoutAccount::where('tenant_id', $tenantId)
            ->latest()
            ->first();

        return Inertia::render('tenant/payouts/Account', [
            'bankOptions' => PayoutBankOption::where('is_active', true)->orderBy('name')->get(),
            'payoutAccount' => $account ? [
                'id' => $account->id,
                'type' => $account->type,
                'account_holder_name' => $account->account_holder_name,
                'bank_name' => $account->bank_name,
                'bank_account_number' => $this->decryptValue($account->bank_account_number),
                'branch_transit_number' => $this->decryptValue($account->branch_transit_number),
                'bank_account_type' => $account->bank_account_type,
                'wipay_account_email' => $this->decryptValue($account->wipay_account_email),
                'status' => $account->status,
                'notes' => $account->notes,
                'review_notes' => $account->review_notes,
                'reviewed_at' => $account->reviewed_at,
                'reviewed_by' => $account->reviewed_by,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $activeBanks = PayoutBankOption::where('is_active', true)->pluck('name')->all();

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:bank,wipay'],
            'account_holder_name' => ['required', 'string', 'max:255'],

            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'branch_transit_number' => ['nullable', 'string', 'max:255'],
            'bank_account_type' => ['nullable', 'string', 'in:savings,checking'],

            'wipay_account_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $tenantId = tenant('id');

        $account = TenantPayoutAccount::where('tenant_id', $tenantId)
            ->latest()
            ->first();

        $isExistingBankWithSavedNumber = $account
            && $account->type === 'bank'
            && ! empty($account->bank_account_number)
            && $validated['type'] === 'bank';

        $isExistingBankWithSavedBranch = $account
            && $account->type === 'bank'
            && ! empty($account->branch_transit_number)
            && $validated['type'] === 'bank';

        $isExistingWipayWithSavedEmail = $account
            && $account->type === 'wipay'
            && ! empty($account->wipay_account_email)
            && $validated['type'] === 'wipay';

        if ($validated['type'] === 'bank') {
            if (empty($validated['bank_name']) || ! in_array($validated['bank_name'], $activeBanks, true)) {
                return back()->withErrors([
                    'bank_name' => 'Please select a valid bank.',
                ])->withInput();
            }

            if (empty($validated['bank_account_type'])) {
                return back()->withErrors([
                    'bank_account_type' => 'Please select Savings or Checking.',
                ])->withInput();
            }

            if (empty($validated['bank_account_number']) && ! $isExistingBankWithSavedNumber) {
                return back()->withErrors([
                    'bank_account_number' => 'Bank account number is required.',
                ])->withInput();
            }

            if (empty($validated['branch_transit_number']) && ! $isExistingBankWithSavedBranch) {
                return back()->withErrors([
                    'branch_transit_number' => 'Branch/transit number is required.',
                ])->withInput();
            }
        }

        if ($validated['type'] === 'wipay' && empty($validated['wipay_account_email']) && ! $isExistingWipayWithSavedEmail) {
            return back()->withErrors([
                'wipay_account_email' => 'WiPay account email is required.',
            ])->withInput();
        }

        if (! $account) {
            $account = new TenantPayoutAccount();
            $account->tenant_id = $tenantId;
        }

        $account->type = $validated['type'];
        $account->account_holder_name = $validated['account_holder_name'];

        if ($validated['type'] === 'bank') {
            $account->bank_name = $validated['bank_name'];
            $account->bank_account_type = $validated['bank_account_type'];

            if (! empty($validated['bank_account_number'])) {
                $account->bank_account_number = Crypt::encryptString($validated['bank_account_number']);
            }

            if (! empty($validated['branch_transit_number'])) {
                $account->branch_transit_number = Crypt::encryptString($validated['branch_transit_number']);
            }

            $account->wipay_account_email = null;
        }

        if ($validated['type'] === 'wipay') {
            if (! empty($validated['wipay_account_email'])) {
                $account->wipay_account_email = Crypt::encryptString($validated['wipay_account_email']);
            }

            $account->bank_name = null;
            $account->bank_account_number = null;
            $account->branch_transit_number = null;
            $account->bank_account_type = null;
        }

        $account->status = 'pending';
        $account->notes = $validated['notes'] ?? null;
        $account->review_notes = null;
        $account->reviewed_at = null;
        $account->reviewed_by = null;
        $account->save();

        return redirect()
            ->route('manage.payout-account')
            ->with('success', 'Payout account saved successfully. It is now pending review.');
    }

    private function decryptValue(?string $encrypted): ?string
    {
        if (! $encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable $e) {
            return 'Hidden';
        }
    }
}
