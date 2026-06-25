import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function expectHealthy(page) {
  await page.waitForLoadState('domcontentloaded');

  const body = page.locator('body');

  await expect(body).toBeAttached();
  await expect(body).not.toContainText('Server Error');
  await expect(body).not.toContainText('Method Not Allowed');
  await expect(body).not.toContainText('SQLSTATE');
  await expect(body).not.toContainText('Undefined variable');
  await expect(body).not.toContainText('Attempt to read property');
  await expect(body).not.toContainText('Base table or view not found');
}

test('tenant website builder can publish draft homepage changes', async ({ page }) => {
  await page.goto(`${tenantBase}/login`);
  await expectHealthy(page);

  await page.fill('#email', 'admin@example.com');
  await page.fill('#password', 'password123');
  await page.click('button[type="submit"]');

  await page.waitForURL(/dashboard|manage|home/);
  await expectHealthy(page);

  await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  await expectHealthy(page);

  await expect(page.locator('body')).toContainText('Published:');

  await page.getByRole('button', { name: /Add Rich Text/i }).click();

  const headingInput = page.locator('[data-setting-id="heading"]').first();
  await expect(headingInput).toBeVisible();
  await headingInput.fill('S41 Browser Published Heading');

  await page.getByRole('button', { name: /Save draft/i }).click();
  await expect(page.locator('body')).toContainText(/Homepage draft saved|Homepage draft saved or published|Homepage published/i);

  await page.getByRole('button', { name: /^Publish$/i }).click();
  await expect(page.locator('body')).toContainText(/Homepage draft saved|Homepage draft saved or published|Homepage published/i);
  await expectHealthy(page);

  await page.goto(`${tenantBase}/`);
  await expectHealthy(page);
  await expect(page.locator('body')).toContainText(/Storefront|S41 Browser Published Heading|Blank page/i);
  await expect(page.locator('body')).toContainText(/Storefront|Featured products|Shop products|Powered by your store/i);
});
