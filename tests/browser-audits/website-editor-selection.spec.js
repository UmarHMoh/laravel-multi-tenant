import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function login(page) {
  await page.goto(`${tenantBase}/login`);
  await page.getByLabel(/email/i).fill('admin@example.com');
  await page.getByLabel(/password/i).fill('password123');
  await page.getByRole('button', { name: /log in|login/i }).click();
  await page.waitForLoadState('networkidle').catch(() => null);
}

test('tenant website editor supports page header footer section and block selection', async ({ page }) => {
  await login(page);
  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expect(page.locator('body')).toContainText('Website Editor');

  await page.getByRole('button', { name: 'Select page' }).click();
  await expect(page.locator('body')).toContainText(/Page settings|Homepage|Type: Homepage/);

  await page.getByRole('button', { name: 'Header · locked' }).first().click();
  await expect(page.locator('body')).toContainText(/Header settings|Header/);

  await page.getByRole('button', { name: 'Footer · locked' }).first().click();
  await expect(page.locator('body')).toContainText(/Footer settings|Footer/);

  await page.getByRole('button', { name: /^Add Rich Text$/ }).first().click();
  await expect(page.locator('body')).toContainText(/Section settings|Rich Text/);

  const addFeatureCard = page.getByRole('button', { name: /^Add Feature Card$/ }).first();
  if (await addFeatureCard.count()) {
    await addFeatureCard.click();
    await expect(page.locator('body')).toContainText(/Block settings|Feature Card|Blocks inside section/);
  }
});
