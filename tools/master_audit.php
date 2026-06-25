<?php

$scripts = [
    'System Audit' => 'tools/system_audit.php',
    'UI Route Audit' => 'tools/ui_route_audit.php',
    'Inertia Prop Audit' => 'tools/inertia_prop_audit.php',
    'Form Validation Model Audit' => 'tools/form_validation_model_audit.php',
    'Tenant Database Audit' => 'tools/tenant_database_audit.php',
    'Tenant Feature Action Audit' => 'tools/tenant_feature_action_audit.php',
    'Full Persona Data Audit' => 'tools/full_persona_data_audit.php',
    'Full Controller Persona Audit' => 'tools/full_controller_persona_audit.php',
    'Page Render Audit' => 'tools/page_render_audit.php',
    'Controller Action Audit' => 'tools/controller_action_audit.php',
    'Dropdown Validation Audit' => 'tools/dropdown_validation_audit.php',
    'Runtime Security Audit' => 'tools/runtime_security_audit.php',
    'Security Middleware Audit' => 'tools/security_middleware_audit.php',
    'Payout Ledger Audit' => 'tools/payout_ledger_audit.php',
    'Payout Request Audit' => 'tools/payout_request_audit.php',
    'Payout Batch Audit' => 'tools/payout_batch_audit.php',
    'Accounting Export Audit' => 'tools/accounting_export_audit.php',
    'Subscription Feature Gate Audit' => 'tools/subscription_feature_gate_audit.php',
    'Storefront Filter Audit' => 'tools/storefront_filter_audit.php',
    'Order Management Audit' => 'tools/order_management_audit.php',
    'Tenant Login Identity Audit' => 'tools/tenant_login_identity_audit.php',
    'Customer Management Audit' => 'tools/customer_management_audit.php',
    'Product Inventory Audit' => 'tools/product_inventory_audit.php',
    'Cart Checkout Stock Audit' => 'tools/cart_checkout_stock_audit.php',
    'Test Payment Transaction Audit' => 'tools/test_payment_transaction_audit.php',
    'Customer Order Experience Audit' => 'tools/customer_order_experience_audit.php',
    'Storefront Navigation Audit' => 'tools/storefront_navigation_audit.php',
    'Website Builder Foundation Audit' => 'tools/website_builder_foundation_audit.php',
    'Theme Section Renderer Audit' => 'tools/theme_section_renderer_audit.php',
    'Builder Plan Limits Audit' => 'tools/builder_plan_limits_audit.php',
    'Website Builder Section Actions Audit' => 'tools/website_builder_section_actions_audit.php',
    'Website Builder Section Settings Audit' => 'tools/website_builder_section_settings_audit.php',
    'Website Builder Blocks Audit' => 'tools/website_builder_blocks_audit.php',
    'Website Builder Publish Audit' => 'tools/website_builder_publish_audit.php',
    'Website Pages Dashboard Audit' => 'tools/website_pages_dashboard_audit.php',
    'Website Editor Shell Audit' => 'tools/website_editor_shell_audit.php',
    'Website Editor Selection Audit' => 'tools/website_editor_selection_audit.php',
    'Website Editor Preview Rendering Audit' => 'tools/website_editor_preview_rendering_audit.php',
    'Website Editor Link Picker Audit' => 'tools/website_editor_link_picker_audit.php',
    'Website Editor Image Picker Audit' => 'tools/website_editor_image_picker_audit.php',
    'Website Editor Reset Audit' => 'tools/website_editor_reset_audit.php',
    'Website Editor Dirty State Audit' => 'tools/website_editor_dirty_state_audit.php',
    'Website Editor Remove Safety Audit' => 'tools/website_editor_remove_safety_audit.php',
    'Website Editor Duplicate Audit' => 'tools/website_editor_duplicate_audit.php',
    'Website Editor Section Search Audit' => 'tools/website_editor_section_search_audit.php',
    'Website Editor Section Categories Audit' => 'tools/website_editor_section_categories_audit.php',
    'Website Editor Section Favorites Audit' => 'tools/website_editor_section_favorites_audit.php',
    'Website Editor Section Presets Audit' => 'tools/website_editor_section_presets_audit.php',
    'Website Editor Rendering Parity Audit' => 'tools/website_editor_rendering_parity_audit.php',
    'Website Editor Media Upload Audit' => 'tools/website_editor_media_upload_audit.php',
    'Website Storefront Editor Parity Audit' => 'tools/website_storefront_editor_parity_audit.php',
    'Website Storefront Visual Parity Audit' => 'tools/website_storefront_visual_parity_audit.php',
    'Website Storefront Product Card Polish Audit' => 'tools/website_storefront_product_card_polish_audit.php',
    'Website Storefront Product Image Data Audit' => 'tools/website_storefront_product_image_data_audit.php',
];

$master = [
    'generated_at' => date('Y-m-d H:i:s'),
    'summary' => [
        'PASS' => 0,
        'FAIL' => 0,
        'WARN' => 0,
        'INFO' => 0,
    ],
    'scripts' => [],
];

foreach ($scripts as $name => $script) {
    if (! file_exists($script)) {
        $master['summary']['FAIL']++;

        $master['scripts'][] = [
            'name' => $name,
            'script' => $script,
            'status' => 'FAIL',
            'message' => 'Audit script file is missing.',
        ];

        continue;
    }

    $command = 'php ' . escapeshellarg($script);
    $output = shell_exec($command);

    $json = json_decode($output, true);

    if (! is_array($json)) {
        $master['summary']['FAIL']++;

        $master['scripts'][] = [
            'name' => $name,
            'script' => $script,
            'status' => 'FAIL',
            'message' => 'Audit script did not return valid JSON.',
            'raw_output_preview' => substr((string) $output, 0, 1000),
        ];

        continue;
    }

    $summary = $json['summary'] ?? [];

    foreach (['PASS', 'FAIL', 'WARN', 'INFO'] as $key) {
        $master['summary'][$key] += (int) ($summary[$key] ?? 0);
    }

    $scriptStatus = ((int) ($summary['FAIL'] ?? 0)) > 0
        ? 'FAIL'
        : (((int) ($summary['WARN'] ?? 0)) > 0 ? 'WARN' : 'PASS');

    $master['scripts'][] = [
        'name' => $name,
        'script' => $script,
        'status' => $scriptStatus,
        'summary' => $summary,
        'failures' => $json['failures'] ?? [],
        'warnings' => $json['warnings'] ?? [],
        'Website Builder Architecture Cleanup Audit' => 'tools/website_builder_architecture_cleanup_audit.php',
    'Website Header Footer Builder Audit' => 'tools/website_header_footer_builder_audit.php',
    'Website Blank Contact Page Audit' => 'tools/website_blank_contact_page_audit.php
website_hero_v2_audit.php',
];
}

echo json_encode($master, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
