import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function login(page) {
  await page.goto(`${tenantBase}/login`);
  await page.getByLabel(/email/i).fill('admin@example.com');
  await page.getByLabel(/password/i).fill('password123');
  await page.getByRole('button', { name: /log in|login/i }).click();
  await page.waitForLoadState('networkidle').catch(() => null);
}

test('tenant website editor exposes header logo upload controls safely', async ({ page }) => {
  await login(page);

  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expect(page.locator('body')).toContainText('Website Editor');

  await page.getByText(/Header/i).first().click();

  await expect(page.locator('[data-header-logo-url-input]')).toBeAttached();
  await expect(page.locator('[data-header-logo-upload-button]')).toBeVisible();
  await expect(page.locator('[data-header-logo-upload-input]')).toBeAttached();

  await page.locator('[data-header-logo-url-input]').fill('/tenant-asset/missing-logo-preview-test.png');

  await expect(page.locator('[data-header-logo-preview]')).toBeAttached();
});
