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

async function centralLogin(page) {
  await page.goto('http://localhost:8000/central/login');
  await page.fill('input[type="email"]', centralEmail);
  await page.fill('input[type="password"]', centralPassword);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/central$/);
  await expectPageHealthy(page);
}

test.describe('Central admin action audit', () => {
  test('central admin can open edit-style pages safely', async ({ page }) => {
    await centralLogin(page);

    const actionPages = [
      'http://localhost:8000/central/payment-settings',
      'http://localhost:8000/central/settings',
      'http://localhost:8000/central/tenants/tenant1',
      'http://localhost:8000/central/payouts/tenant1',
    ];

    for (const url of actionPages) {
      await page.goto(url);
      await expectPageHealthy(page);

      const buttons = page.locator('button');
      const count = await buttons.count();

      expect(count).toBeGreaterThan(0);
    }
  });
});

test.describe('Tenant admin action audit', () => {
  test('tenant manage pages expose usable action buttons/forms', async ({ page }) => {
    const pages = [
      'http://tenant1.localhost:8000/manage/billing',
      'http://tenant1.localhost:8000/manage/store-settings',
      'http://tenant1.localhost:8000/manage/payout-account',
      'http://tenant1.localhost:8000/manage/product',
      'http://tenant1.localhost:8000/manage/category',
      'http://tenant1.localhost:8000/manage/order',
    ];

    for (const url of pages) {
      await page.goto(url);
      await expectPageHealthy(page);

      const forms = await page.locator('form').count();
      const buttons = await page.locator('button').count();
      const links = await page.locator('a').count();

      expect(forms + buttons + links).toBeGreaterThan(0);
    }
  });
});

test.describe('Storefront customer action audit', () => {
  test('tenant storefront pages expose customer navigation/actions', async ({ page }) => {
    const pages = [
      'http://tenant1.localhost:8000/home',
      'http://tenant1.localhost:8000/cart',
    ];

    for (const url of pages) {
      await page.goto(url);
      await expectPageHealthy(page);

      const buttons = await page.locator('button').count();
      const links = await page.locator('a').count();

      expect(buttons + links).toBeGreaterThan(0);
    }
  });
});

test('storefront search and filters are usable when product grid exists, otherwise blank storefront stays healthy', async ({ page }) => {
  await page.goto('http://tenant1.localhost:8000/home');
  await expectPageHealthy(page);

  const searchInput = page.locator('input[type="search"]').first();
  const searchCount = await searchInput.count();

  if (searchCount > 0) {
    await searchInput.fill('audit');
    await page.keyboard.press('Enter');
    await expectPageHealthy(page);

    const sortSelect = page.locator('select').last();
    if (await sortSelect.count()) {
      await sortSelect.selectOption('price_low');
      await expectPageHealthy(page);
    }

    await expect(page.locator('body')).toContainText(/Products|No products found|Shop products/i);
    return;
  }

  await expect(page.locator('[data-storefront-header="true"]').first()).toBeVisible();
  await expect(page.locator('[data-storefront-footer="true"]').first()).toBeVisible();
});

