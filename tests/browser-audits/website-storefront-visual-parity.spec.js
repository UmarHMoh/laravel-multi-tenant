import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

test('tenant storefront renders visual parity styling hooks', async ({ page }) => {
  await page.goto(`${tenantBase}/`);

  await expect(page.locator('[data-storefront-builder-homepage]').first()).toBeVisible();
  await expect(page.locator('[data-storefront-visual-parity-card]').first()).toBeVisible();
  await expect(page.locator('body')).toContainText('Storefront visual parity active');
  await expect(page.locator('body')).toContainText(/Live storefront sections now share editor-style spacing|Storefront visual parity active/);
});
