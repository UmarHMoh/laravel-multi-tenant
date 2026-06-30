<?php

$checks = [];
$failures = [];

function s70_check(string $name, bool $passed, string $message = ''): void
{
    global $checks, $failures;

    $checks[] = [
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $message,
    ];

    if (! $passed) {
        $failures[] = [
            'name' => $name,
            'message' => $message,
        ];
    }
}

$registry = file_get_contents(__DIR__ . '/../app/Services/Themes/SectionRegistry.php');
$home = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/Homepage.vue');
$productDetail = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/ProductDetail.vue');
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s70_check('reviews comments schema exists', str_contains($registry, "'reviews_comments' =>"), 'reviews_comments schema missing.');
s70_check('reviews comments placeholder settings exist', str_contains($registry, 'placeholder_mode') && str_contains($registry, 'show_rating_summary') && str_contains($registry, 'show_comment_box'), 'Placeholder settings missing.');
s70_check('homepage reviews comments placeholder renderer exists', str_contains($home, 'data-reviews-comments-placeholder') && str_contains($home, 'data-comment-box-placeholder'), 'Homepage reviews/comments placeholder missing.');
s70_check('homepage rating summary placeholder exists', str_contains($home, 'data-rating-summary-placeholder'), 'Homepage rating summary placeholder missing.');
s70_check('product detail reviews comments placeholder exists', str_contains($productDetail, 'data-product-reviews-comments-placeholder') && str_contains($productDetail, 'data-product-comment-box-placeholder'), 'Product detail reviews/comments placeholder missing.');
s70_check('product rating summary placeholder exists', str_contains($productDetail, 'data-product-rating-summary-placeholder'), 'Product rating summary placeholder missing.');
s70_check('editor reviews comments foundation exists', str_contains($editor, 'data-s70-reviews-comments-editor') && str_contains($editor, 's70CreateReviewsCommentsSection'), 'Editor reviews/comments foundation missing.');
s70_check('placeholder clearly says working reviews later', str_contains($editor, 'Working reviews later') || str_contains($home, 'later stage') || str_contains($productDetail, 'enabled later'), 'Working-later language missing.');

$summary = [
    'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
    'FAIL' => count(array_filter($checks, fn ($check) => $check['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'script' => 'website_reviews_comments_placeholder_audit',
    'summary' => $summary,
    'checks' => $checks,
    'failures' => $failures,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
