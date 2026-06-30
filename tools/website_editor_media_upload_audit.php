<?php

$checks = [];
$failures = [];

function s57_check(string $section, string $name, bool $condition, string $message, array $data = []): void
{
    global $checks, $failures;

    $row = [
        'section' => $section,
        'name' => $name,
        'status' => $condition ? 'PASS' : 'FAIL',
        'message' => $message,
        'data' => $data,
    ];

    $checks[] = $row;

    if (! $condition) {
        $failures[] = $row;
    }
}

$controller = file_get_contents(__DIR__ . '/../app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s57_check('website_editor_media_upload', 'controller upload method exists', str_contains($controller, 'public function uploadMedia(Request $request)'), 'uploadMedia controller method found.');
s57_check('website_editor_media_upload', 'controller validates image upload', str_contains($controller, "'image' =>") && str_contains($controller, "'mimes:jpg,jpeg,png,webp,gif'"), 'image validation found.');
s57_check('website_editor_media_upload', 'controller stores image on public disk', (str_contains($controller, "store('tenant-website', 'public')") || str_contains($controller, "storeAs('tenant-website'")), 'public disk storage found.');
s57_check('website_editor_media_upload', 'controller returns storage url', str_contains($controller, 'Storage::url($path)'), 'Storage::url return found.');
s57_check('website_editor_media_upload', 'media upload route exists', str_contains($routes, "uploadMedia") && str_contains($routes, "tenant.website.media.upload"), 'media upload route found.');
s57_check('website_editor_media_upload', 'editor upload helper exists', str_contains($editor, 'function uploadEditorImage(event, applyUrl)'), 'uploadEditorImage helper found.');
s57_check('website_editor_media_upload', 'editor csrf helper exists', str_contains($editor, 'editorCsrfToken'), 'CSRF helper found.');
s57_check('website_editor_media_upload', 'editor file input exists', str_contains($editor, 'data-editor-image-upload-input'), 'file input found.');
s57_check('website_editor_media_upload', 'editor file accept types exists', str_contains($editor, 'accept="image/png,image/jpeg,image/webp,image/gif"'), 'image accept types found.');
s57_check('website_editor_media_upload', 'editor upload UI label exists', str_contains($editor, 'Upload image from device'), 'upload UI label found.');
s57_check('website_editor_media_upload', 'stage marker exists', str_contains($editor, 'S57 media upload foundation'), 'stage marker found.');

echo json_encode([
    'summary' => [
        'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
        'FAIL' => count($failures),
        'WARN' => 0,
        'INFO' => 0,
    ],
    'generated_at' => date('Y-m-d H:i:s'),
    'failures' => $failures,
    'warnings' => [],
    'all_findings' => $checks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
