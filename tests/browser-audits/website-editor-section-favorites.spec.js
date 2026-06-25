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

test('tenant website editor can pin favorite available sections', async ({ page }) => {
  await page.goto(`${tenantBase}/login`);
  await page.evaluate(() => {
    window.localStorage.removeItem('website_editor_favorite_section_types');
  });
  await expectHealthy(page);

  await page.fill('#email', 'admin@example.com');
  await page.fill('#password', 'password123');
  await page.click('button[type="submit"]');

  await page.waitForURL(/dashboard|manage|home/);
  await expectHealthy(page);

  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expectHealthy(page);

  const favorites = page.locator('[data-section-favorites]');

  await expect(favorites).toContainText('Favorite sections');
  await expect(favorites).toContainText('Pin sections you use often for quick access.');

  await page.getByRole('button', { name: /^Pin Rich Text$/ }).click();

  await expect(page.getByRole('button', { name: /^Pinned Rich Text$/ })).toBeVisible();
  await expect(favorites.getByRole('button', { name: /^Add pinned Rich Text$/ })).toBeVisible();

  await page.reload();
  await expectHealthy(page);

  const reloadedFavorites = page.locator('[data-section-favorites]');
  await expect(reloadedFavorites.getByRole('button', { name: /^Add pinned Rich Text$/ })).toBeVisible();

  await reloadedFavorites.getByRole('button', { name: /^Add pinned Rich Text$/ }).click();
  await expect(page.locator('body')).toContainText('Unsaved changes');
});
