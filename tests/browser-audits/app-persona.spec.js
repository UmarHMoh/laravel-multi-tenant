import { test, expect } from '@playwright/test';

const centralEmail = 'umarhmohammed04@gmail.com';
const centralPassword = 'password123';

async function expectPageHealthy(page) {
  await expect(page.locator('body')).toBeVisible();
  await expect(page.locator('body')).not.toContainText('Server Error');
  await expect(page.locator('body')).not.toContainText('Method Not Allowed');
  await expect(page.locator('body')).not.toContainText('SQLSTATE');
  await expect(page.locator('body')).not.toContainText('Undefined variable');
  await expect(page.locator('body')).not.toContainText('Attempt to read property');
}

test.describe('Central admin browser audit', () => {
  test('central admin login and core pages load', async ({ page }) => {
    await page.goto('http://localhost:8000/central');
    await expect(page).toHaveURL(/central\/login/);
    await expectPageHealthy(page);

    await page.fill('input[type="email"]', centralEmail);
    await page.fill('input[type="password"]', centralPassword);
    await page.click('button[type="submit"]');

    await page.waitForURL(/\/central$/);
    await expectPageHealthy(page);

    const centralPages = [
      '/central',
      '/central/tenants',
      '/central/tenants/tenant1',
      '/central/payouts',
      '/central/payouts/tenant1',
      '/central/payment-settings',
      '/central/settings',
      '/central/bank-options',
    ];

    for (const path of centralPages) {
      await page.goto(`http://localhost:8000${path}`);
      await expectPageHealthy(page);
    }
  });
});

test.describe('Tenant browser audit', () => {
  test('tenant1 public and manage pages load', async ({ page }) => {
    const pages = [
      'http://tenant1.localhost:8000/home',
      'http://tenant1.localhost:8000/cart',
      'http://tenant1.localhost:8000/login',
      'http://tenant1.localhost:8000/dashboard',
      'http://tenant1.localhost:8000/manage/billing',
      'http://tenant1.localhost:8000/manage/store-settings',
      'http://tenant1.localhost:8000/manage/payout-account',
      'http://tenant1.localhost:8000/manage/product',
      'http://tenant1.localhost:8000/manage/category',
      'http://tenant1.localhost:8000/manage/order',
      'http://tenant1.localhost:8000/manage/customer',
    ];

    for (const url of pages) {
      await page.goto(url);
      await expectPageHealthy(page);
    }
  });
});
