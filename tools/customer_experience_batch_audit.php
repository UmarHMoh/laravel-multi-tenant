<?php

$findings = [];

function addFinding(string $name, bool $passed, string $message, array $data = []): void
{
    global $findings;

    $findings[] = [
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $message,
        'data' => $data,
    ];
}

function fileContains(string $path, string $needle): bool
{
    return file_exists($path) && str_contains(file_get_contents($path), $needle);
}

$homepage = 'resources/js/pages/tenant/Homepage.vue';
$cart = 'resources/js/pages/tenant/ShoppingCartList.vue';
$checkout = 'resources/js/pages/tenant/Payment.vue';
$confirmation = 'resources/js/pages/tenant/OrderConfirmation.vue';
$orders = 'resources/js/pages/tenant/orders/CustomerIndex.vue';

foreach ([
    'data-s101-live-product-grid-search',
    'data-s101-live-product-grid-category',
    'data-s101-live-product-grid-sort',
    'data-s101-product-grid-reset',
    'data-s101-product-grid-empty',
    'productGridState',
    'productGridCategories',
    'resetProductGridState',
] as $needle) {
    addFinding("Homepage contains {$needle}", fileContains($homepage, $needle), "{$needle} should exist in Homepage.");
}

foreach ([
    'data-s102-cart-page-polish',
    'data-cart-stock-warning',
    'data-cart-line-subtotal',
    'data-cart-estimated-delivery',
    'data-cart-checkout-button',
    'cartHasLowStock',
    'estimatedDeliveryText',
] as $needle) {
    addFinding("Cart contains {$needle}", fileContains($cart, $needle), "{$needle} should exist in cart page.");
}

foreach ([
    'data-s103-checkout-polish',
    'data-checkout-progress',
    'data-checkout-submit',
    'checkoutStepLabels',
    'checkoutProgress',
] as $needle) {
    addFinding("Checkout contains {$needle}", fileContains($checkout, $needle), "{$needle} should exist in checkout page.");
}

foreach ([
    'data-s104-order-confirmation-polish',
    'data-order-timeline',
    'data-order-print-button',
    'orderTimelineSteps',
] as $needle) {
    addFinding("Order confirmation contains {$needle}", fileContains($confirmation, $needle), "{$needle} should exist in order confirmation page.");
}

foreach ([
    'data-s105-customer-orders-polish',
    'data-customer-order-filters',
    'data-customer-order-search',
    'data-customer-order-status',
    'data-customer-order-card',
    'filteredOrders',
    'orderSearch',
    'orderStatus',
] as $needle) {
    addFinding("Customer orders contains {$needle}", fileContains($orders, $needle), "{$needle} should exist in customer orders page.");
}

$failures = array_values(array_filter(
    $findings,
    fn ($finding) => ($finding['status'] ?? null) === 'FAIL'
));

$summary = [
    'PASS' => count(array_filter($findings, fn ($finding) => ($finding['status'] ?? null) === 'PASS')),
    'FAIL' => count($failures),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => date('Y-m-d H:i:s'),
    'failures' => $failures,
    'warnings' => [],
    'all_findings' => $findings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
