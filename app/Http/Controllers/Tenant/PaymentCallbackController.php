<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Services\Payments\PlatformTransactionService;
use App\Services\Payments\TenantPayoutLedgerService;
use App\Services\Payments\UniversalPaymentGateway;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function return(Request $request, Order $order)
    {
        $setting = PaymentSetting::active() ?? PaymentSetting::latest()->first();

        if (! $setting) {
            return redirect()
                ->route('orders.confirmation', $order->id)
                ->with('error', 'No active payment processor is configured.');
        }

        $verification = app(UniversalPaymentGateway::class)->verifyPayment(
            order: $order,
            setting: $setting,
            incoming: $request->all()
        );

        $this->applyVerification($order, $setting, $verification);

        return redirect()
            ->route('orders.confirmation', $order->id)
            ->with(
                ($verification['paid'] ?? false) ? 'success' : 'error',
                ($verification['paid'] ?? false)
                    ? 'Payment confirmed successfully.'
                    : 'Payment could not be confirmed yet. Your order remains pending.'
            );
    }

    public function cancel(Request $request, Order $order)
    {
        $order->update([
            'payment_status' => $order->payment_status === 'paid' ? 'paid' : 'cancelled',
            'payment_metadata' => array_merge($order->payment_metadata ?? [], [
                'payment_cancelled_at' => now()->toDateTimeString(),
                'cancel_payload' => $request->all(),
            ]),
        ]);

        return redirect()
            ->route('orders.confirmation', $order->id)
            ->with('error', 'Payment was cancelled. Your order was not marked as paid.');
    }

    public function webhook(Request $request, ?string $provider = null)
    {
        $setting = PaymentSetting::active() ?? PaymentSetting::latest()->first();

        if (! $setting) {
            return response()->json([
                'success' => false,
                'message' => 'No active payment processor configured.',
            ], 422);
        }

        if (! $this->hasValidWebhookSignature($request, $setting)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], 401);
        }

        $payload = $request->all();

        $orderId = data_get($payload, 'order_id')
            ?? data_get($payload, 'metadata.order_id')
            ?? data_get($payload, 'custom.order_id');

        $orderNumber = data_get($payload, 'order_number')
            ?? data_get($payload, 'metadata.order_number')
            ?? data_get($payload, 'custom.order_number');

        $order = null;

        if ($orderId) {
            $order = Order::find($orderId);
        }

        if (! $order && $orderNumber) {
            $order = Order::where('order_number', $orderNumber)->first();
        }

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found for webhook payload.',
            ], 404);
        }

        $verification = app(UniversalPaymentGateway::class)->verifyFromIncomingPayload(
            order: $order,
            setting: $setting,
            incoming: $payload
        );

        $this->applyVerification($order, $setting, $verification);

        return response()->json([
            'success' => true,
            'paid' => (bool) ($verification['paid'] ?? false),
            'order_id' => $order->id,
            'payment_status' => $order->fresh()->payment_status,
        ]);
    }


    private function hasValidWebhookSignature(Request $request, PaymentSetting $setting): bool
    {
        if (! $setting->webhook_secret) {
            return true;
        }

        $provided = $request->header('X-Webhook-Signature')
            ?? $request->header('X-Signature')
            ?? $request->header('X-Hub-Signature-256');

        if (! $provided) {
            return false;
        }

        $provided = str_replace('sha256=', '', (string) $provided);

        $computed = hash_hmac('sha256', $request->getContent(), $setting->webhook_secret);

        return hash_equals($computed, $provided);
    }


    private function applyVerification(Order $order, PaymentSetting $setting, array $verification): void
    {
        $metadata = array_merge($order->payment_metadata ?? [], [
            'last_payment_verification' => $verification,
            'last_payment_verification_at' => now()->toDateTimeString(),
        ]);

        $updates = [
            'provider_payment_id' => $verification['provider_payment_id'] ?? $order->provider_payment_id,
            'provider_reference' => $verification['provider_reference'] ?? $order->provider_reference,
            'payment_metadata' => $metadata,
        ];

        $wasAlreadyPaid = $order->payment_status === 'paid';

        if (($verification['paid'] ?? false) && ! $wasAlreadyPaid) {
            $updates['payment_status'] = 'paid';
            $updates['status'] = 'processing';
            $updates['paid_at'] = now();
        }

        $order->update($updates);

        if (($verification['paid'] ?? false) && ! $wasAlreadyPaid) {
            $transaction = app(PlatformTransactionService::class)->recordPaidOrder(
                tenant: tenant(),
                order: $order->fresh(),
                provider: $setting->provider . '_verified_payment'
            );

            app(TenantPayoutLedgerService::class)->recordPaidOrder($transaction);
        }
    }
}
