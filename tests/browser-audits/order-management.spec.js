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
    throw new Error(`Tenant login failed and stayed on login page. Body: ${bodyText.slice(0, 500)}`);
  }
}

test('tenant order management page loads and filters safely', async ({ page }) => {
  await loginTenant(page);

  await page.goto('http://tenant1.localhost:8000/manage/order');
  await expectPageHealthy(page);

  await expect(page.locator('body')).toContainText(/Orders|No orders found/i);

  const searchInput = page.locator('input[type="search"]').first();
  await expect(searchInput).toBeVisible();
  await searchInput.fill('audit');
  await page.keyboard.press('Enter');
  await expectPageHealthy(page);

  const statusSelect = page.locator('select').first();
  await expect(statusSelect).toBeVisible();
  await statusSelect.selectOption('pending');
  await expectPageHealthy(page);
});
