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

test('tenant website editor shows dirty state and clears after save', async ({ page }) => {
  await page.goto(`${tenantBase}/login`);
  await expectHealthy(page);

  await page.fill('#email', 'admin@example.com');
  await page.fill('#password', 'password123');
  await page.click('button[type="submit"]');

  await page.waitForURL(/dashboard|manage|home/);
  await expectHealthy(page);

  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expectHealthy(page);

  await expect(page.locator('body')).toContainText('Website Editor');
  await expect(page.locator('body')).toContainText(/Saved|Homepage draft saved/i);

  await page.getByRole('button', { name: 'Add Rich Text' }).click();
  await expect(page.locator('body')).toContainText('Unsaved changes');

  await page.getByRole('button', { name: 'Save draft' }).click();
  await expect(page.locator('body')).toContainText(/Saved|Homepage draft saved/i);
});
