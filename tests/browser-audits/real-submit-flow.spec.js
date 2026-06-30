import { test, expect } from '@playwright/test';
import { execSync, execFileSync } from 'node:child_process';
import { writeFileSync, unlinkSync } from 'node:fs';
import fs from 'node:fs';
import path from 'node:path';

const tenantBase = 'http://tenant1.localhost:8000';
const tenantEmail = 'admin@example.com';
const tenantPassword = 'password123';

const runPhp = (code) => {
  const file = path.resolve('.playwright-temp-run.php');

  fs.writeFileSync(file, `<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\\Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

${code}
`);

  try {
    return execSync(`php ${JSON.stringify(file)}`, {
      encoding: 'utf8',
      stdio: ['pipe', 'pipe', 'pipe'],
    });
  } finally {
    try {
      fs.unlinkSync(file);
    } catch (e) {
      // ignore cleanup failure
    }
  }
};

async function expectPageHealthy(page) {
  await page.waitForLoadState('domcontentloaded');

  const html = page.locator('html');
  const body = page.locator('body');

  await expect(html).toBeAttached();
  await expect(body).toBeAttached();

  await expect(body).not.toContainText('Server Error');
  await expect(body).not.toContainText('Method Not Allowed');
  await expect(body).not.toContainText('SQLSTATE');
  await expect(body).not.toContainText('Undefined variable');
  await expect(body).not.toContainText('Attempt to read property');
  await expect(body).not.toContainText('Exception');
}

function cleanup(unique) {
  runPhp(`
    $tenant = \\App\\Models\\Tenant::find("tenant1");
    tenancy()->initialize($tenant);

    $products = \\App\\Models\\Product::where("name", "like", "%${unique}%")->get();
    $productIds = $products->pluck("id")->all();

    if (count($productIds) > 0) {
        $orderIds = \\App\\Models\\OrderItem::whereIn("product_id", $productIds)->pluck("order_id")->unique()->all();

        \\App\\Models\\OrderItem::whereIn("product_id", $productIds)->delete();

        if (count($orderIds) > 0) {
            \\App\\Models\\Order::whereIn("id", $orderIds)->delete();
        }

        \\App\\Models\\CartItem::whereIn("product_id", $productIds)->delete();
        \\App\\Models\\ProductImage::whereIn("product_id", $productIds)->delete();
        \\App\\Models\\Product::whereIn("id", $productIds)->delete();
    }

    \\App\\Models\\Category::where("name", "like", "%${unique}%")->delete();

    tenancy()->end();

    echo "cleaned";
  `);
}

function getProductId(unique) {
  const output = runPhp(`
    $tenant = \\App\\Models\\Tenant::find("tenant1");
    tenancy()->initialize($tenant);

    $product = \\App\\Models\\Product::where("name", "like", "%${unique}%")->latest()->first();

    echo $product ? $product->id : "";

    tenancy()->end();
  `);

  return output.trim();
}

function getOrderCount(unique) {
  const output = runPhp(`
    $tenant = \\App\\Models\\Tenant::find("tenant1");
    tenancy()->initialize($tenant);

    $productIds = \\App\\Models\\Product::where("name", "like", "%${unique}%")->pluck("id")->all();
    $orderIds = \\App\\Models\\OrderItem::whereIn("product_id", $productIds)->pluck("order_id")->unique()->all();

    echo count($orderIds);

    tenancy()->end();
  `);

  return Number(output.trim() || 0);
}

test.describe('Real browser submit flow', () => {
  test('admin creates category/product and customer places order', async ({ browser }) => {
    const unique = `PW-${Date.now()}`;
    const categoryName = `Playwright Category ${unique}`;
    const productName = `Playwright Product ${unique}`;

    cleanup(unique);

    try {
      const adminContext = await browser.newContext();
      const adminPage = await adminContext.newPage();

      await adminPage.goto(`${tenantBase}/login`);
      await expectPageHealthy(adminPage);

      await adminPage.fill('#email', tenantEmail);
      await adminPage.fill('#password', tenantPassword);
      await adminPage.click('button[type="submit"]');

      await adminPage.waitForURL(/dashboard|manage|home/);
      await expectPageHealthy(adminPage);

      await adminPage.goto(`${tenantBase}/manage/category/create`);
      await expectPageHealthy(adminPage);

      await adminPage.fill('#name', categoryName);
      await adminPage.fill('#description', `Browser submit category ${unique}`);
      await adminPage.click('button[type="submit"]');

      await adminPage.waitForURL(/manage\/category/);
      await expectPageHealthy(adminPage);
      await expect(adminPage.locator('body')).toContainText(categoryName);

      await adminPage.goto(`${tenantBase}/manage/product/create`);
      await expectPageHealthy(adminPage);

      await adminPage.fill('#name', productName);
      await adminPage.selectOption('#category', { label: categoryName });
      await adminPage.fill('#price', '99.99');
      await adminPage.fill('#stock', '5');
      await adminPage.fill('#description', `Browser submit product ${unique}`);
      await adminPage.click('button[type="submit"]');

      await adminPage.waitForURL(/manage\/product/);
      await expectPageHealthy(adminPage);
      await expect(adminPage.locator('body')).toContainText(productName);

      await adminContext.close();

      const productId = getProductId(unique);
      expect(productId).not.toBe('');

      const customerContext = await browser.newContext();
      const customerPage = await customerContext.newPage();

      customerPage.on('dialog', async (dialog) => {
        await dialog.accept();
      });

      await customerPage.goto(`${tenantBase}/products/${productId}`);
      await expectPageHealthy(customerPage);
      await expect(customerPage.locator('body')).toContainText(productName);

      await customerPage.getByRole('button', { name: /add to cart/i }).click();

      await customerPage.goto(`${tenantBase}/cart`);
      await expectPageHealthy(customerPage);
      await expect(customerPage.locator('body')).toContainText(productName);

      await customerPage.getByRole('link', { name: /proceed to checkout/i }).click();
      await customerPage.waitForURL(/checkout/);
      await expectPageHealthy(customerPage);

      // Checkout inputs do not have label-for/id pairs, so fill by stable field order.
      await customerPage.locator('input[type="text"]').nth(0).fill('Playwright Customer');
      await customerPage.locator('input[type="email"]').fill(`customer-${unique}@example.com`);
      await customerPage.locator('input[type="tel"]').fill('8681234567');
      await customerPage.locator('input[type="text"]').nth(1).fill('123 Playwright Street');
      await customerPage.locator('input[type="text"]').nth(2).fill('San Fernando');
      await customerPage.locator('input[type="text"]').nth(3).fill('South');
      await customerPage.locator('input[type="text"]').nth(4).fill('00000');

      await customerPage.check('#bank_transfer');

      await customerPage.locator('textarea').last().fill(`Browser checkout note ${unique}`);

      const orderButton = customerPage.getByRole('button', { name: /place order|submit order|complete order|confirm order|pay now/i });
      await expect(orderButton).toBeVisible();
      await orderButton.click();

      await customerPage.waitForURL(/orders\/confirmation/);
      await expectPageHealthy(customerPage);
      await expect(customerPage.locator('body')).toContainText('Thank you for your order');
      await expect(customerPage.locator('body')).toContainText(productName);

      const orderCount = getOrderCount(unique);
      expect(orderCount).toBeGreaterThan(0);

      await customerContext.close();
    } finally {
      cleanup(unique);
    }
  });
});


function cleanupPlaywrightTenantData() {
  const cleanupFile = 'storage/app/playwright-tenant-cleanup.php'

  const php = String.raw`<?php
require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

tenancy()->initialize('tenant1');

$products = \App\Models\Product::query()
    ->where('name', 'like', 'Playwright Product PW-%')
    ->get();

foreach ($products as $product) {
    \App\Models\ThemePage::query()
        ->where('product_id', $product->id)
        ->orWhere('title', 'like', 'Playwright Product PW-%')
        ->delete();

    if (method_exists($product, 'images')) {
        $product->images()->delete();
    }

    $product->delete();
}

\App\Models\Category::query()
    ->where(function ($query) {
        $query->where('name', 'like', 'Playwright Category PW-%')
            ->orWhere('name', 'like', 'Audit Category PW-%');
    })
    ->whereDoesntHave('products')
    ->delete();

echo "Playwright tenant cleanup complete\n";
`

  writeFileSync(cleanupFile, php)

  try {
    execFileSync('php', [cleanupFile], { stdio: 'inherit' })
  } finally {
    try {
      unlinkSync(cleanupFile)
    } catch {
      // ignore cleanup file deletion errors
    }
  }
}

test.afterEach(() => {
  cleanupPlaywrightTenantData()
})

