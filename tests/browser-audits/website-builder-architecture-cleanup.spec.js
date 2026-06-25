import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function login(page) {
  await page.goto(`${tenantBase}/login`);
  await page.getByLabel(/email/i).fill('admin@example.com');
  await page.getByLabel(/password/i).fill('password123');
  await Promise.all([
    page.waitForLoadState('networkidle').catch(() => null),
    page.getByRole('button', { name: /log in|login/i }).click(),
  ]);
}

test('website builder architecture cleanup keeps editor columns and blank homepage support', async ({ page }) => {
  await login(page);
  await page.goto(`${tenantBase}/manage/website/homepage/editor`);

  await expect(page.locator('body')).toContainText('Website Editor');
  await expect(page.locator('body')).toContainText('Header · locked');
  await expect(page.locator('body')).toContainText('Footer · locked');
  await expect(page.locator('[data-editor-right-sidebar-restored]').first()).toBeVisible();
});

test('tenant storefront remains tenant-scoped and header/footer rendered', async ({ page }) => {
  await page.goto(`${tenantBase}/home`);

  await expect(page.locator('[data-storefront-header="true"]').first()).toBeVisible();
  await expect(page.locator('[data-storefront-footer="true"]').first()).toBeVisible();
  await expect(page.locator('body')).not.toContainText('Server Error');
  await expect(page.locator('body')).not.toContainText('SQLSTATE');
});
