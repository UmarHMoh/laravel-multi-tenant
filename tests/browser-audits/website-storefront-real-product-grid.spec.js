import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

test('tenant storefront renders real product grid surface', async ({ page }) => {
  await page.goto(`${tenantBase}/`);

  await expect(page.locator('[data-storefront-real-product-grid]').first()).toBeVisible();
  await expect(page.locator('[data-storefront-real-product-grid-card]').first()).toBeVisible();
  await expect(page.locator('body')).toContainText('Featured products');
  await expect(page.locator('body')).toContainText('Shop products');
});
