import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

test('tenant storefront exposes editor rendering parity markers', async ({ page }) => {
  await page.goto(`${tenantBase}/`);
  await expect(page.locator('body')).toContainText(/Tenant Storefront|Welcome to your store|Storefront preview parity/i);
  await expect(page.locator('body')).toContainText('Storefront preview parity');
  await expect(page.locator('body')).toContainText('Product cards automatically link to product detail pages');
});
