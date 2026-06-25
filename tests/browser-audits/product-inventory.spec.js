import { test, expect } from '@playwright/test';

async function expectPageHealthy(page) {
  await expect(page.locator('body')).not.toContainText(/Server Error|Exception|SQLSTATE|Vite manifest/i);
}

async function loginTenant(page) {
  await page.goto('http://tenant1.localhost:8000/login');
  await expectPageHealthy(page);

  await page.locator('#email').fill('admin@example.com');
  await page.locator('#password').fill('password123');
  await page.getByRole('button', { name: /^log in$/i }).click();

  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  if (page.url().includes('/login')) {
    const bodyText = await page.locator('body').innerText();
    throw new Error(`Tenant login failed. Body: ${bodyText.slice(0, 500)}`);
  }
}

test('tenant product inventory page loads and filters safely', async ({ page }) => {
  await loginTenant(page);

  await page.goto('http://tenant1.localhost:8000/manage/product');
  await expectPageHealthy(page);

  await expect(page.locator('body')).toContainText(/Products|No products found/i);
  await expect(page.locator('body')).toContainText(/Inventory Units|Low Stock|Out of Stock/i);

  const searchInput = page.locator('input[type="search"]').first();
  await expect(searchInput).toBeVisible();
  await searchInput.fill('inventory');
  await page.keyboard.press('Enter');
  await expectPageHealthy(page);

  const stockSelect = page.locator('select').first();
  await expect(stockSelect).toBeVisible();
  await stockSelect.selectOption('low');
  await expectPageHealthy(page);
});
