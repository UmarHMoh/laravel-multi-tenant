import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

test('tenant storefront renders real product image data section', async ({ page }) => {
  await page.goto(`${tenantBase}/`);

  await expect(page.locator('[data-storefront-real-product-image-section]').first()).toBeVisible();
  await expect(page.locator('body')).toContainText('Storefront product image data active');
  await expect(page.locator('body')).toContainText(/Real product image rendering|Storefront product image data active/);
  await expect(page.locator('body')).toContainText('Product cards now read real image fields');
});
