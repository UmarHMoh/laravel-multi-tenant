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

test('tenant website editor can add ready-made section presets', async ({ page }) => {
  await page.goto(`${tenantBase}/login`);
  await expectHealthy(page);

  await page.fill('#email', 'admin@example.com');
  await page.fill('#password', 'password123');
  await page.click('button[type="submit"]');

  await page.waitForURL(/dashboard|manage|home/);
  await expectHealthy(page);

  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expectHealthy(page);

  const presets = page.locator('[data-section-presets]');

  await expect(presets).toContainText('Section presets');
  await expect(presets).toContainText('Add ready-made section layouts with starter content.');

  await expect(presets.getByRole('button', { name: /Add preset Hero with CTA/ })).toBeVisible();
  await expect(presets.getByRole('button', { name: /Add preset Hero Minimal/ })).toBeVisible();
  await expect(presets.getByRole('button', { name: /Add preset Product Feature/ })).toBeVisible();
  await expect(presets.getByRole('button', { name: /Add preset Text Block/ })).toBeVisible();

  await presets.getByRole('button', { name: /Add preset Hero with CTA/ }).click();

  await expect(page.locator('body')).toContainText('Unsaved changes');
  await expect(page.locator('body')).toContainText('Build your dream store');
});
