<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

function audit_result(string $section, string $status, string $message, array $data = []): array
{
    return [
        'section' => $section,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

function vue_page_path(string $page): string
{
    return 'resources/js/pages/' . $page . '.vue';
}

function find_matching_bracket(string $text, int $start, string $open, string $close): ?int
{
    $depth = 0;
    $len = strlen($text);
    $quote = null;
    $escape = false;

    for ($i = $start; $i < $len; $i++) {
        $char = $text[$i];

        if ($quote !== null) {
            if ($escape) {
                $escape = false;
                continue;
            }

            if ($char === '\\') {
                $escape = true;
                continue;
            }

            if ($char === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($char === '"' || $char === "'") {
            $quote = $char;
            continue;
        }

        if ($char === $open) {
            $depth++;
        }

        if ($char === $close) {
            $depth--;

            if ($depth === 0) {
                return $i;
            }
        }
    }

    return null;
}

function split_top_level_array_entries(string $arrayText): array
{
    $entries = [];
    $current = '';
    $depthSquare = 0;
    $depthParen = 0;
    $depthBrace = 0;
    $quote = null;
    $escape = false;

    $len = strlen($arrayText);

    for ($i = 0; $i < $len; $i++) {
        $char = $arrayText[$i];

        if ($quote !== null) {
            $current .= $char;

            if ($escape) {
                $escape = false;
                continue;
            }

            if ($char === '\\') {
                $escape = true;
                continue;
            }

            if ($char === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($char === '"' || $char === "'") {
            $quote = $char;
            $current .= $char;
            continue;
        }

        if ($char === '[') $depthSquare++;
        if ($char === ']') $depthSquare--;
        if ($char === '(') $depthParen++;
        if ($char === ')') $depthParen--;
        if ($char === '{') $depthBrace++;
        if ($char === '}') $depthBrace--;

        if ($char === ',' && $depthSquare === 0 && $depthParen === 0 && $depthBrace === 0) {
            if (trim($current) !== '') {
                $entries[] = trim($current);
            }

            $current = '';
            continue;
        }

        $current .= $char;
    }

    if (trim($current) !== '') {
        $entries[] = trim($current);
    }

    return $entries;
}

function extract_top_level_props_from_array(string $arrayText): array
{
    $props = [];

    foreach (split_top_level_array_entries($arrayText) as $entry) {
        if (preg_match('/^[\'"]([A-Za-z0-9_]+)[\'"]\s*=>/', $entry, $match)) {
            $props[] = $match[1];
        }
    }

    return array_values(array_unique($props));
}

function extract_inertia_renders(string $controllerText, string $controllerFile): array
{
    $renders = [];
    $offset = 0;

    while (($pos = strpos($controllerText, 'Inertia::render', $offset)) !== false) {
        $parenStart = strpos($controllerText, '(', $pos);

        if ($parenStart === false) {
            break;
        }

        $parenEnd = find_matching_bracket($controllerText, $parenStart, '(', ')');

        if ($parenEnd === null) {
            break;
        }

        $call = substr($controllerText, $parenStart + 1, $parenEnd - $parenStart - 1);

        if (! preg_match('/^\s*[\'"]([^\'"]+)[\'"]/', $call, $pageMatch)) {
            $offset = $parenEnd + 1;
            continue;
        }

        $page = $pageMatch[1];
        $controllerProps = [];

        $commaPos = strpos($call, ',');

        if ($commaPos !== false) {
            $afterComma = trim(substr($call, $commaPos + 1));

            if (str_starts_with($afterComma, '[')) {
                $arrayStartInCall = strpos($call, '[', $commaPos);
                $arrayEndInCall = find_matching_bracket($call, $arrayStartInCall, '[', ']');

                if ($arrayEndInCall !== null) {
                    $arrayText = substr($call, $arrayStartInCall + 1, $arrayEndInCall - $arrayStartInCall - 1);
                    $controllerProps = extract_top_level_props_from_array($arrayText);
                }
            }
        }

        $renders[] = [
            'controller_file' => $controllerFile,
            'page' => $page,
            'vue_file' => vue_page_path($page),
            'controller_props' => $controllerProps,
        ];

        $offset = $parenEnd + 1;
    }

    return $renders;
}

function extract_vue_props(string $vueText): array
{
    $props = [];

    preg_match('/defineProps\s*\(\s*\{(.*?)\}\s*\)/s', $vueText, $match);

    if (isset($match[1])) {
        preg_match_all('/^\s*([A-Za-z0-9_]+)\s*:/m', $match[1], $propMatches);
        $props = array_merge($props, $propMatches[1] ?? []);
    }

    preg_match('/defineProps\s*<\s*\{(.*?)\}\s*>\s*\(\s*\)/s', $vueText, $tsMatch);

    if (isset($tsMatch[1])) {
        preg_match_all('/^\s*([A-Za-z0-9_]+)\s*[?:]\s*/m', $tsMatch[1], $tsPropMatches);
        $props = array_merge($props, $tsPropMatches[1] ?? []);
    }

    return array_values(array_unique(array_filter($props)));
}

$controllerFiles = collect(glob(base_path('app/Http/Controllers/**/*.php'), GLOB_BRACE))
    ->unique()
    ->values();

$findings = [];
$renderCount = 0;

foreach ($controllerFiles as $controllerFile) {
    $relativeController = str_replace(base_path() . '/', '', $controllerFile);
    $text = file_get_contents($controllerFile);

    if (! str_contains($text, 'Inertia::render')) {
        continue;
    }

    foreach (extract_inertia_renders($text, $relativeController) as $render) {
        $renderCount++;

        $vuePath = base_path($render['vue_file']);

        if (! file_exists($vuePath)) {
            $findings[] = audit_result('inertia_props', 'FAIL', 'Controller renders a Vue page that does not exist.', $render);
            continue;
        }

        $vueText = file_get_contents($vuePath);
        $vueProps = extract_vue_props($vueText);
        $controllerProps = $render['controller_props'];

        $missingFromController = array_values(array_diff($vueProps, $controllerProps));
        $extraFromController = array_values(array_diff($controllerProps, $vueProps));

        if (count($missingFromController) > 0) {
            $findings[] = audit_result('inertia_props', 'FAIL', 'Vue expects props the controller does not send.', [
                ...$render,
                'vue_props' => $vueProps,
                'missing_from_controller' => $missingFromController,
                'extra_from_controller' => $extraFromController,
            ]);

            continue;
        }

        if (count($vueProps) === 0 && count($controllerProps) > 0) {
            $findings[] = audit_result('inertia_props', 'WARN', 'Controller sends props but Vue does not declare defineProps. Manual review needed.', [
                ...$render,
                'vue_props' => $vueProps,
                'extra_from_controller' => $extraFromController,
            ]);

            continue;
        }

        $findings[] = audit_result('inertia_props', count($extraFromController) > 0 ? 'WARN' : 'PASS', count($extraFromController) > 0
            ? 'Controller sends top-level props not declared by Vue. Usually safe, but review.'
            : 'Controller props match Vue defineProps.',
            [
                ...$render,
                'vue_props' => $vueProps,
                'missing_from_controller' => $missingFromController,
                'extra_from_controller' => $extraFromController,
            ]
        );
    }
}

$summary = [
    'PASS' => 0,
    'FAIL' => 0,
    'WARN' => 0,
    'INFO' => 0,
];

foreach ($findings as $finding) {
    $summary[$finding['status']] = ($summary[$finding['status']] ?? 0) + 1;
}

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'controller_render_count' => $renderCount,
    'failures' => array_values(array_filter($findings, fn ($item) => $item['status'] === 'FAIL')),
    'warnings' => array_values(array_filter($findings, fn ($item) => $item['status'] === 'WARN')),
    'all_findings' => $findings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
