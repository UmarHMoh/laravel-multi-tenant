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

test('tenant website builder foundation page loads safely', async ({ page }) => {
  await page.goto(`${tenantBase}/login`);
  await expectHealthy(page);

  await page.fill('#email', 'admin@example.com');
  await page.fill('#password', 'password123');
  await page.click('button[type="submit"]');

  await page.waitForURL(/dashboard|manage|home/);
  await expectHealthy(page);

  await page.goto(`${tenantBase}/manage/website`);
  await expectHealthy(page);

  await expect(page.locator('body')).toContainText('Website Builder');
  await expect(page.locator('body')).toContainText('Pages');
  await expect(page.locator('body')).toContainText('Create page');
  await expect(page.locator('body')).toContainText('Open homepage editor');
  await expect(page.locator('body')).toContainText('Draft + published workflow');
});
