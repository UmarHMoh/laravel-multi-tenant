<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UniversalPaymentGateway
{
    public function canCreateCheckout(?PaymentSetting $setting): bool
    {
        return (bool) (
            $setting
            && $setting->is_active
            && $setting->base_url
            && $setting->checkout_endpoint
        );
    }

    public function createCheckout(Order $order, PaymentSetting $setting): array
    {
        if (! $this->canCreateCheckout($setting)) {
            return [
                'success' => false,
                'message' => 'The active payment processor is not fully configured for online checkout.',
                'provider' => $setting?->provider,
            ];
        }

        $payload = $this->buildPayload($order, $setting);
        $headers = $this->buildHeaders($setting);

        $url = rtrim((string) $setting->base_url, '/') . '/' . ltrim((string) $setting->checkout_endpoint, '/');

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->acceptJson()
                ->post($url, $payload);

            $json = $response->json();

            if (! is_array($json)) {
                $json = [
                    'raw_body' => $response->body(),
                ];
            }

            $mapped = $this->mapResponse($json, $setting);

            return [
                'success' => $response->successful() && (bool) ($mapped['checkout_url'] ?? null),
                'message' => $response->successful()
                    ? 'Checkout session created.'
                    : 'Payment processor returned an unsuccessful response.',
                'provider' => $setting->provider,
                'status' => $response->status(),
                'payload' => $payload,
                'response' => $json,
                'checkout_url' => $mapped['checkout_url'] ?? null,
                'provider_payment_id' => $mapped['provider_payment_id'] ?? null,
                'provider_reference' => $mapped['provider_reference'] ?? null,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'provider' => $setting->provider,
                'payload' => $payload,
            ];
        }
    }


    public function verifyPayment(Order $order, PaymentSetting $setting, array $incoming = []): array
    {
        if (! $setting->verify_endpoint) {
            return $this->verifyFromIncomingPayload($order, $setting, $incoming);
        }

        $headers = $this->buildHeaders($setting);

        $url = rtrim((string) $setting->base_url, '/') . '/' . ltrim((string) $setting->verify_endpoint, '/');

        $payload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'provider_payment_id' => $order->provider_payment_id,
            'provider_reference' => $order->provider_reference,
        ];

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->acceptJson()
                ->post($url, $payload);

            $json = $response->json();

            if (! is_array($json)) {
                $json = [
                    'raw_body' => $response->body(),
                ];
            }

            return $this->mapVerificationResponse($json, $setting, $response->successful(), $response->status());
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'paid' => false,
                'message' => $e->getMessage(),
                'provider' => $setting->provider,
            ];
        }
    }

    public function verifyFromIncomingPayload(Order $order, PaymentSetting $setting, array $incoming = []): array
    {
        return $this->mapVerificationResponse($incoming, $setting, true, 200);
    }

    private function mapVerificationResponse(array $response, PaymentSetting $setting, bool $httpSuccessful = true, ?int $status = null): array
    {
        $mapping = is_array($setting->response_mapping) ? $setting->response_mapping : [];

        $statusValue = $this->getValueByPath($response, $mapping['payment_status'] ?? null)
            ?? $response['payment_status']
            ?? $response['status']
            ?? $response['transaction_status']
            ?? null;

        $paidValues = ['paid', 'success', 'successful', 'approved', 'completed', 'captured'];

        $paid = in_array(strtolower((string) $statusValue), $paidValues, true)
            || ($response['paid'] ?? false) === true
            || ($response['success'] ?? false) === true;

        return [
            'success' => $httpSuccessful,
            'paid' => $paid,
            'message' => $paid ? 'Payment verified as paid.' : 'Payment is not confirmed as paid.',
            'provider' => $setting->provider,
            'status' => $status,
            'payment_status' => $statusValue,
            'provider_payment_id' => $this->getValueByPath($response, $mapping['provider_payment_id'] ?? null)
                ?? $response['payment_id']
                ?? $response['transaction_id']
                ?? $response['id']
                ?? null,
            'provider_reference' => $this->getValueByPath($response, $mapping['provider_reference'] ?? null)
                ?? $response['reference']
                ?? $response['order_reference']
                ?? null,
            'response' => $response,
        ];
    }


    private function buildPayload(Order $order, PaymentSetting $setting): array
    {
        $defaultPayload = [
            'amount' => (float) $order->total,
            'currency' => $order->payment_metadata['currency'] ?? $setting->currency ?? 'TTD',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->billing_name,
            'customer_email' => $order->billing_email,
            'customer_phone' => $order->billing_phone,
            'description' => 'Order ' . $order->order_number,
            'success_url' => route('payment.return', $order->id),
            'cancel_url' => route('payment.cancel', $order->id),
            'metadata' => [
                'tenant_id' => tenant('id'),
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ];

        $template = $setting->request_template;

        if (! is_array($template) || count($template) === 0) {
            return $defaultPayload;
        }

        return $this->replaceTokens($template, $defaultPayload);
    }

    private function buildHeaders(PaymentSetting $setting): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($setting->auth_type === 'api_key' && $setting->api_key) {
            $headers['Authorization'] = 'Bearer ' . $setting->api_key;
            $headers['X-API-Key'] = $setting->api_key;
        }

        if ($setting->auth_type === 'bearer_token' && $setting->bearer_token) {
            $headers['Authorization'] = 'Bearer ' . $setting->bearer_token;
        }

        if (is_array($setting->headers_template)) {
            $headers = array_merge($headers, $this->replaceTokens($setting->headers_template, [
                'api_key' => $setting->api_key,
                'public_key' => $setting->public_key,
                'secret_key' => $setting->secret_key,
                'merchant_id' => $setting->merchant_id,
                'account_id' => $setting->account_id,
                'bearer_token' => $setting->bearer_token,
            ]));
        }

        return array_filter($headers, fn ($value) => $value !== null && $value !== '');
    }

    private function mapResponse(array $response, PaymentSetting $setting): array
    {
        $mapping = is_array($setting->response_mapping) ? $setting->response_mapping : [];

        return [
            'checkout_url' => $this->getValueByPath($response, $mapping['checkout_url'] ?? null)
                ?? $response['checkout_url']
                ?? $response['payment_url']
                ?? $response['url']
                ?? $response['redirect_url']
                ?? null,

            'provider_payment_id' => $this->getValueByPath($response, $mapping['provider_payment_id'] ?? null)
                ?? $response['payment_id']
                ?? $response['transaction_id']
                ?? $response['id']
                ?? null,

            'provider_reference' => $this->getValueByPath($response, $mapping['provider_reference'] ?? null)
                ?? $response['reference']
                ?? $response['order_reference']
                ?? null,
        ];
    }

    private function replaceTokens(array $template, array $values): array
    {
        $json = json_encode($template);

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $json = str_replace('{{' . $key . '}}', (string) $value, $json);
        }

        return json_decode($json, true) ?: [];
    }

    private function getValueByPath(array $data, ?string $path): mixed
    {
        if (! $path) {
            return null;
        }

        return data_get($data, $path);
    }
}
