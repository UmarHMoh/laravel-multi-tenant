import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function expectHealthy(page) {
  await page.waitForLoadState('domcontentloaded');

  const body = page.locator('body');
  await expect(body).toBeAttached();
  await expect(body).not.toContainText('Server Error');
  await expect(body).not.toContainText('SQLSTATE');
  await expect(body).not.toContainText('Undefined variable');
  await expect(body).not.toContainText('Attempt to read property');
}

test('tenant website editor has smart fuzzy section search', async ({ page }) => {
  await page.goto(`${tenantBase}/login`);
  await expectHealthy(page);

  await page.fill('#email', 'admin@example.com');
  await page.fill('#password', 'password123');
  await page.click('button[type="submit"]');

  await page.waitForURL(/dashboard|manage|home/);
  await expectHealthy(page);

  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expectHealthy(page);

  await expect(page.locator('body')).toContainText('Available sections');
  await expect(page.locator('body')).toContainText('Smart search handles apostrophes, plurals, and small spelling mistakes.');

  const search = page.locator('#section-search');
  await expect(search).toBeVisible();

  await search.fill('herro');
  await expect(page.locator('body')).toContainText(/Add Hero Banner|Hero Banner/);

  await search.fill('prodct');
  await expect(page.locator('body')).toContainText(/Product Grid|Add Product Grid/);

  await search.fill('teext');
  await expect(page.locator('body')).toContainText(/Add Rich Text|Rich Text/);

  await search.fill('zzzzzzzz');
  await expect(page.locator('body')).toContainText('No matching sections found. Try a shorter word.');
});
