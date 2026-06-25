import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

test('tenant storefront renders product card polish hooks', async ({ page }) => {
  await page.goto(`${tenantBase}/`);

  await expect(page.locator('[data-storefront-product-card-polish]').first()).toBeVisible();
  await expect(page.locator('body')).toContainText('Storefront product card polish active');
  await expect(page.locator('body')).toContainText(/Product cards now support image placeholders|Storefront product card polish active/);
});
