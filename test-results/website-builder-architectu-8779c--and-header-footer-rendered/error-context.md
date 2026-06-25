# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: website-builder-architecture-cleanup.spec.js >> tenant storefront remains tenant-scoped and header/footer rendered
- Location: tests/browser-audits/website-builder-architecture-cleanup.spec.js:25:1

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('[data-storefront-header="true"]').first()
Expected: visible
Timeout: 10000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 10000ms
  - waiting for locator('[data-storefront-header="true"]').first()

```

```yaml
- banner:
  - img
  - text: Internal Server Error
  - button:
    - img
- main:
  - text: ErrorException Undefined array key "name" GET tenant1.localhost:8000 PHP 8.4.22 — Laravel 12.7.2
  - button "Expand vendor frames":
    - text: Expand vendor frames
    - img
    - img
  - button "app/Services/Themes/ThemePageRenderer.php :26 array_map"
  - text: 2 vendor frames collapsed
  - button "app/Services/Themes/ThemePageRenderer.php :16 renderableSections"
  - button "App\\Http\\Controllers\\Tenant\\HomepageController :26 index"
  - text: 5 vendor frames collapsed
  - button "app/Http/Middleware/EnsureTenantIsActive.php :24 handle"
  - text: 5 vendor frames collapsed
  - button "App\\Http\\Middleware\\HandleAppearance :21 handle"
  - text: 1 vendor frame collapsed
  - button "app/Http/Middleware/EnsureCentralAdminAuthenticated.php :14 handle"
  - text: 48 vendor frames collapsed
  - button "public/index.php :20 require_once"
  - text: 1 vendor frame collapsed app/Services/Themes/ThemePageRenderer.php :26
  - code:
    - table:
      - rowgroup:
        - 'row "21 }"':
          - cell "21"
          - 'cell "}"'
        - row "22":
          - cell "22"
          - cell
        - row "23 return [":
          - cell "23"
          - cell "return ["
        - row "24 'id' => $section['id'] ?? ('section_' . uniqid()),":
          - cell "24"
          - cell "'id' => $section['id'] ?? ('section_' . uniqid()),"
        - row "25 'type' => $section['type'],":
          - cell "25"
          - cell "'type' => $section['type'],"
        - row "26 'name' => $schema['name'],":
          - cell "26"
          - cell "'name' => $schema['name'],"
        - row "27 'settings' => $this->settingsWithDefaults($schema, $section['settings'] ?? []),":
          - cell "27"
          - cell "'settings' => $this->settingsWithDefaults($schema, $section['settings'] ?? []),"
        - row "28 'blocks' => $this->visibleBlocks($section['blocks'] ?? []),":
          - cell "28"
          - cell "'blocks' => $this->visibleBlocks($section['blocks'] ?? []),"
        - row "29 ];":
          - cell "29"
          - cell "];"
        - 'row "30 })"':
          - cell "30"
          - 'cell "})"'
        - row "31 ->filter()":
          - cell "31"
          - cell "->filter()"
        - row "32 ->values()":
          - cell "32"
          - cell "->values()"
        - row "33 ->all();":
          - cell "33"
          - cell "->all();"
        - 'row "34 }"':
          - cell "34"
          - 'cell "}"'
        - row "35":
          - cell "35"
          - cell
        - 'row "36 public function homepageData(array $sections): array"':
          - cell "36"
          - 'cell "public function homepageData(array $sections): array"'
        - 'row "37 {"':
          - cell "37"
          - 'cell "{"'
  - text: Request GET /home Headers host
  - code: tenant1.localhost:8000
  - text: connection
  - code: keep-alive
  - text: sec-ch-ua
  - code: "\"HeadlessChrome\";v=\"149\", \"Chromium\";v=\"149\", \"Not)A;Brand\";v=\"24\""
  - text: sec-ch-ua-mobile
  - code: "?0"
  - text: sec-ch-ua-platform
  - code: "\"macOS\""
  - text: upgrade-insecure-requests
  - code: "1"
  - text: user-agent
  - code: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/149.0.7827.55 Safari/537.36
  - text: accept-language
  - code: en-US
  - text: accept
  - code: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
  - text: sec-fetch-site
  - code: none
  - text: sec-fetch-mode
  - code: navigate
  - text: sec-fetch-user
  - code: "?1"
  - text: sec-fetch-dest
  - code: document
  - text: accept-encoding
  - code: gzip, deflate, br, zstd
  - text: Body
  - code: No body data
  - text: Application Routing controller
  - code: App\Http\Controllers\Tenant\HomepageController@index
  - text: route name
  - code: home
  - text: middleware
  - code: web, Stancl\Tenancy\Middleware\InitializeTenancyByDomain, Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains, App\Http\Middleware\EnsureTenantIsActive
  - text: Database Queries mysql (1.22 ms)
  - code: "select * from `tenants` where exists (select * from `domains` where `tenants`.`id` = `domains`.`tenant_id` and `domain` = 'tenant1.localhost') limit 1"
  - text: mysql (0.31 ms)
  - code: "select * from `domains` where `domains`.`tenant_id` in ('tenant1')"
  - text: mysql (0.34 ms)
  - code: SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'tenant_tenant1'
  - text: mysql (0.32 ms)
  - code: "select * from `tenants` where `tenants`.`id` = 'tenant1' limit 1"
  - text: tenant (0.95 ms)
  - code: "select * from `themes` where `is_active` = '1' order by `id` desc limit 1"
  - text: tenant (0.5 ms)
  - code: "update `themes` set `settings` = '{\"colors\":{\"primary\":\"#111827\",\"secondary\":\"#ffffff\"},\"header\":{\"enabled\":true,\"logo_text\":\"Aromniac\",\"logo_image_url\":\"https:\\/\\/www.instagram.com\\/aromaniac.tt\\/\",\"logo_position\":\"left\",\"links\":[{\"label\":\"Shop\",\"url\":\"\\/home\"},{\"label\":\"Contact\",\"url\":\"\\/pages\\/contact\"},{\"label\":\"Cart\",\"url\":\"\\/cart\"}],\"mobile_menu\":true},\"footer\":{\"enabled\":true,\"text\":\"Powered by your Hasan Marketing.\",\"links\":[{\"label\":\"Shop\",\"url\":\"\\/home\"},{\"label\":\"Contact\",\"url\":\"\\/pages\\/contact\"}]}}', `themes`.`updated_at` = '2026-06-25 17:34:22' where `id` = 1"
  - text: tenant (0.42 ms)
  - code: "select * from `theme_pages` where (`theme_id` = 1 and `handle` = 'home') limit 1"
  - text: tenant (0.37 ms)
  - code: "select * from `theme_pages` where (`theme_id` = 1 and `handle` = 'contact') limit 1"
  - text: tenant (0.46 ms)
  - code: "select * from `themes` where `id` = 1 limit 1"
  - text: tenant (0.43 ms)
  - code: "select * from `theme_pages` where `theme_pages`.`theme_id` in (1)"
  - text: tenant (0.61 ms)
  - code: "select * from `theme_pages` where `theme_pages`.`theme_id` = 1 and `theme_pages`.`theme_id` is not null and `type` = 'home' limit 1"
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | const tenantBase = 'http://tenant1.localhost:8000';
  4  | 
  5  | async function login(page) {
  6  |   await page.goto(`${tenantBase}/login`);
  7  |   await page.getByLabel(/email/i).fill('admin@example.com');
  8  |   await page.getByLabel(/password/i).fill('password123');
  9  |   await Promise.all([
  10 |     page.waitForLoadState('networkidle').catch(() => null),
  11 |     page.getByRole('button', { name: /log in|login/i }).click(),
  12 |   ]);
  13 | }
  14 | 
  15 | test('website builder architecture cleanup keeps editor columns and blank homepage support', async ({ page }) => {
  16 |   await login(page);
  17 |   await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  18 | 
  19 |   await expect(page.locator('body')).toContainText('Website Editor');
  20 |   await expect(page.locator('body')).toContainText('Header · locked');
  21 |   await expect(page.locator('body')).toContainText('Footer · locked');
  22 |   await expect(page.locator('[data-editor-right-sidebar-restored]').first()).toBeVisible();
  23 | });
  24 | 
  25 | test('tenant storefront remains tenant-scoped and header/footer rendered', async ({ page }) => {
  26 |   await page.goto(`${tenantBase}/home`);
  27 | 
> 28 |   await expect(page.locator('[data-storefront-header="true"]').first()).toBeVisible();
     |                                                                         ^ Error: expect(locator).toBeVisible() failed
  29 |   await expect(page.locator('[data-storefront-footer="true"]').first()).toBeVisible();
  30 |   await expect(page.locator('body')).not.toContainText('Server Error');
  31 |   await expect(page.locator('body')).not.toContainText('SQLSTATE');
  32 | });
  33 | 
```