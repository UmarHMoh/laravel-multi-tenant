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

test('tenant website editor preview has polished responsive rendering controls', async ({ page }) => {
  await page.goto(`${tenantBase}/login`);
  await expectHealthy(page);

  await page.fill('#email', 'admin@example.com');
  await page.fill('#password', 'password123');
  await page.click('button[type="submit"]');

  await page.waitForURL(/dashboard|manage|home/);
  await expectHealthy(page);

  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expectHealthy(page);

  await expect(page.locator('body')).toContainText('Live preview');
  await expect(page.locator('body')).toContainText('Storefront preview');
  await expect(page.locator('body')).toContainText('Storefront preview');

  await page.getByRole('button', { name: /^Mobile$/i }).click();
  await expect(page.locator('body')).toContainText('mobile view');

  await page.getByRole('button', { name: /^Tablet$/i }).click();
  await expect(page.locator('body')).toContainText('tablet view');

  await page.getByRole('button', { name: /^Desktop$/i }).click();
  await expect(page.locator('body')).toContainText('desktop view');

  await expect(page.locator('body')).toContainText('S45 preview rendering polish');
});
