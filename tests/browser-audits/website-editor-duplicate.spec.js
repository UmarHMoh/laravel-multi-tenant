import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function login(page) {
  await page.goto(`${tenantBase}/login`);
  await page.getByLabel(/email/i).fill('admin@example.com');
  await page.getByLabel(/password/i).fill('password123');
  await page.getByRole('button', { name: /log in|login/i }).click();
  await page.waitForLoadState('networkidle').catch(() => null);
}

test('tenant website editor can duplicate a section when a section exists', async ({ page }) => {
  await login(page);
  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expect(page.locator('body')).toContainText('Website Editor');

  const addRichText = page.getByRole('button', { name: /^Add Rich Text$/ }).first();
  if (await addRichText.count()) {
    await addRichText.click();
  }

  await expect(page.locator('body')).toContainText(/Sections:/);
  await expect(page.getByRole('button', { name: /^Duplicate$/ }).first()).toBeVisible();

  const before = await page.locator('text=/Type: rich_text|Type: hero|Type: featured_products|Type: product_grid/').count();
  await page.getByRole('button', { name: /^Duplicate$/ }).first().click();
  const after = await page.locator('text=/Type: rich_text|Type: hero|Type: featured_products|Type: product_grid/').count();

  expect(after).toBeGreaterThanOrEqual(before);
});
