import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function login(page) {
  await page.goto(`${tenantBase}/login`);
  await page.getByLabel(/email/i).fill('admin@example.com');
  await page.getByLabel(/password/i).fill('password123');
  await page.getByRole('button', { name: /log in|login/i }).click();
  await page.waitForLoadState('networkidle').catch(() => null);
}

test('tenant website editor exposes hero image picker foundation', async ({ page }) => {
  await login(page);
  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expect(page.locator('body')).toContainText('Website Editor');

  await page.getByRole('button', { name: /^Add Hero Banner$/ }).first().click();
  await expect(page.locator('body')).toContainText(/Hero image|Desktop hero image|Upload image from device|Choose an image file from your computer/);
  await expect(page.locator('[data-editor-image-upload-input]').first()).toBeAttached();
});
