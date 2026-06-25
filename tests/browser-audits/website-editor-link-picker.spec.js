import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function login(page) {
  await page.goto(`${tenantBase}/login`);
  await page.getByLabel(/email/i).fill('admin@example.com');
  await page.getByLabel(/password/i).fill('password123');
  await page.getByRole('button', { name: /log in|login/i }).click();
  await page.waitForLoadState('networkidle').catch(() => null);
}

test('tenant website editor exposes link picker for URL settings', async ({ page }) => {
  await login(page);
  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expect(page.locator('body')).toContainText('Website Editor');

  await page.getByRole('button', { name: /^Add Hero Banner$/ }).first().click();
  await expect(page.locator('body')).toContainText(/CTA button link|Custom URL|Home page|Pick an internal page or paste a custom URL/);
});
