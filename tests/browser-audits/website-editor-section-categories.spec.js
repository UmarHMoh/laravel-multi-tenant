import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function expectHealthy(page) {
  await page.waitForLoadState('domcontentloaded');

  const body = page.locator('body');
  await expect(body).toBeAttached();
  await expect(body).not.toContainText('Server Error');
  await expect(body).not.toContainText('SQLSTATE');
  await expect(body).not.toContainText('Undefined variable');
  await expect(body).not.toContainText('Attempt to read property');
}

test('tenant website editor groups available sections by category', async ({ page }) => {
  await page.goto(`${tenantBase}/login`);
  await expectHealthy(page);

  await page.fill('#email', 'admin@example.com');
  await page.fill('#password', 'password123');
  await page.click('button[type="submit"]');

  await page.waitForURL(/dashboard|manage|home/);
  await expectHealthy(page);

  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expectHealthy(page);

  await expect(page.locator('body')).toContainText('Available sections');
  await expect(page.locator('body')).toContainText('Section categories');

  const categoryFilters = page.locator('[data-section-category-filters]');

  await expect(categoryFilters.getByRole('button', { name: /^All \(/ })).toBeVisible();
  await expect(categoryFilters.getByRole('button', { name: /^Hero \(/ })).toBeVisible();
  await expect(categoryFilters.getByRole('button', { name: /^Products \(/ })).toBeVisible();

  await categoryFilters.getByRole('button', { name: /^Products \(/ }).click();
  await expect(page.locator('body')).toContainText(/Product Grid|Add Product Grid/);
  await expect(page.getByRole('button', { name: /Add Featured Products/ })).toBeVisible();
  await expect(page.getByRole('button', { name: /Add Rich Text/ })).toHaveCount(0);

  const search = page.locator('#section-search');
  await search.fill('prodct');
  await expect(page.locator('body')).toContainText(/Product Grid|Add Product Grid/);

  await categoryFilters.getByRole('button', { name: /^All \(/ }).click();
  await search.fill('teext');
  await expect(page.locator('body')).toContainText(/Add Rich Text|Rich Text/);
});
