import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function login(page) {
  await page.goto(`${tenantBase}/login`);
  await page.getByLabel(/email/i).fill('admin@example.com');
  await page.getByLabel(/password/i).fill('password123');
  await page.getByRole('button', { name: /log in|login/i }).click();
  await page.waitForLoadState('networkidle').catch(() => null);
}

test('tenant website editor keeps header and footer locked but selectable', async ({ page }) => {
  await login(page);
  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expect(page.locator('body')).toContainText('Website Editor');

  const header = page.getByRole('button', { name: 'Header · locked' }).first();
  const footer = page.getByRole('button', { name: 'Footer · locked' }).first();

  await expect(header).toBeVisible();
  await expect(footer).toBeVisible();

  await header.click();
  await expect(page.locator('body')).toContainText(/Header settings|Header is locked|Header/);

  await footer.click();
  await expect(page.locator('body')).toContainText(/Footer settings|Footer is locked|Footer/);
});
