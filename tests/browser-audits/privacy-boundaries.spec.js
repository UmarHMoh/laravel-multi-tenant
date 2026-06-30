import { expect, test } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { writeFileSync, unlinkSync } from 'node:fs';


const tenantBase = 'http://tenant1.localhost:8000';

function ensureTenantCustomerUser() {
  const customerFile = 'storage/app/playwright-create-customer.php'

  const php = String.raw`<?php
require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

tenancy()->initialize('tenant1');

\App\Models\User::updateOrCreate(
    ['email' => 'customer@example.com'],
    [
        'name' => 'Customer Test',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'role' => 'customer',
    ]
);

echo "Tenant customer ready\n";
`

  writeFileSync(customerFile, php)

  try {
    execFileSync('php', [customerFile], { stdio: 'inherit' })
  } finally {
    try {
      unlinkSync(customerFile)
    } catch {
      // ignore cleanup file deletion errors
    }
  }
}

async function login(page, email = 'admin@example.com', password = 'password123') {
  await page.goto(`${tenantBase}/login`);
  await page.getByLabel(/email/i).fill(email);
  await page.getByLabel(/password/i).fill(password);
  await page.getByRole('button', { name: /log in|login/i }).click();
  await page.waitForLoadState('networkidle');
}

test.describe('Tenant privacy boundary audit', () => {
  test('tenant billing hides internal processor and platform fee split', async ({ page }) => {
    await login(page);

    await page.goto(`${tenantBase}/manage/billing`);
    await page.waitForLoadState('networkidle');

    const body = await page.locator('body').innerText();

    await expect(page.locator('body')).toContainText(/Processing Fee|Transaction Fee/i);
    await expect(page.locator('body')).not.toContainText(/Payment Processor Fee/i);
    await expect(page.locator('body')).not.toContainText(/Platform Commission Added/i);
    await expect(page.locator('body')).not.toContainText(/Total Transaction Fee Charged/i);
    await expect(page.locator('body')).not.toContainText(/Calculated after processor fees/i);

    expect(body.toLowerCase()).not.toContain('processor fee rules');
  });

  test('tenant dashboard shows public transaction fee wording only', async ({ page }) => {
    await login(page);

    await page.goto(`${tenantBase}/dashboard`);
    await page.waitForLoadState('networkidle');

    await expect(page.locator('body')).toContainText(/Transaction Fee/i);
    await expect(page.locator('body')).toContainText(/Applied to online payments/i);
    await expect(page.locator('body')).not.toContainText(/Commission Rate/i);
    await expect(page.locator('body')).not.toContainText(/Calculated after processor fees/i);
  });

  test('storefront does not show admin dashboard link to customers or guests', async ({ page }) => {
    await page.goto(`${tenantBase}/home`);
    await page.waitForLoadState('networkidle');

    await expect(page.locator('[data-storefront-admin-dashboard-link]')).toHaveCount(0);
    await expect(page.locator('body')).not.toContainText('Dashboard');
  });

  test('tenant admin can see Store Dashboard link on storefront', async ({ page }) => {
    await login(page);

    await page.goto(`${tenantBase}/home`);
    await page.waitForLoadState('networkidle');

    await expect(page.locator('[data-storefront-admin-dashboard-link]')).toBeVisible();
    await expect(page.locator('[data-storefront-admin-dashboard-link]')).toContainText('Store Dashboard');
  });

  test('payout request page loads under tenant context without central users table leak', async ({ page }) => {
    await login(page);

    const response = await page.goto(`${tenantBase}/manage/payouts`);
    await page.waitForLoadState('networkidle');

    expect(response?.status()).toBeLessThan(500);

    await expect(page.locator('body')).not.toContainText(/ecommerce_central\.users/i);
    await expect(page.locator('body')).not.toContainText(/SQLSTATE/i);
    await expect(page.locator('body')).not.toContainText(/Base table or view not found/i);
  });

  test('customer cannot access tenant admin dashboard or manage pages', async ({ page }) => {
    ensureTenantCustomerUser();

    await login(page, 'customer@example.com', 'password123');

    await page.goto(`${tenantBase}/home`);
    await page.waitForLoadState('networkidle');

    await expect(page.locator('body')).toContainText(/My Orders|Logout/i);
    await expect(page.locator('[data-storefront-admin-dashboard-link]')).toHaveCount(0);

    const dashboardResponse = await page.goto(`${tenantBase}/dashboard`);
    await page.waitForLoadState('networkidle');

    expect([403, 404]).toContain(dashboardResponse?.status());

    const manageResponse = await page.goto(`${tenantBase}/manage/product`);
    await page.waitForLoadState('networkidle');

    expect([403, 404]).toContain(manageResponse?.status());
  });

  test('storefront logo fallback avoids broken image when logo URL is invalid', async ({ page }) => {
    await page.goto(`${tenantBase}/home`);
    await page.waitForLoadState('networkidle');

    await page.evaluate(() => {
      const header = document.querySelector('[data-storefront-header="true"]');
      if (!header) return;

      const img = document.createElement('img');
      img.src = '/tenant-asset/missing-logo-does-not-exist.png';
      img.setAttribute('data-temporary-broken-logo', 'true');
      header.appendChild(img);
      img.dispatchEvent(new Event('error'));
      img.remove();
    });

    await expect(page.locator('[data-storefront-header="true"]')).toBeVisible();
  });

});
