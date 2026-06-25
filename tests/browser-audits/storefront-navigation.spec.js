import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

test('tenant storefront navigation exposes shop cart login and order links safely', async ({ page }) => {
  await page.goto(`${tenantBase}/home`, { waitUntil: 'domcontentloaded' });

  await expect(page.locator('body')).toContainText(/Storefront|Shop|Cart/);
  await expect(page.locator('body')).toContainText(/Login|Register|Dashboard|My Orders|Logout/);

  await expect(page.locator('a[href="/home"]').first()).toBeVisible();
  await expect(page.locator('a[href="/cart"]').first()).toBeVisible();

  const authenticatedLinks = page.locator('text=/My Orders|Logout|Dashboard/');
  const guestLinks = page.locator('text=/Login|Register/');

  const authCount = await authenticatedLinks.count();
  const guestCount = await guestLinks.count();

  expect(authCount + guestCount).toBeGreaterThan(0);
});
