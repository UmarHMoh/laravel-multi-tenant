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

test('tenant website editor can reset homepage draft to defaults', async ({ page }) => {
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

  page.once('dialog', async dialog => {
    expect(dialog.message()).toContain('Reset this draft to default sections?');
    await dialog.accept();
  });

  await Promise.all([
    page.waitForResponse(response =>
      response.url().includes('/manage/website/homepage/reset') && response.status() < 500
    ),
    page.getByRole('button', { name: /^Reset$/ }).click(),
  ]);

  await page.reload();
  await expectHealthy(page);
  await expect(page.locator('body')).toContainText('Sections:');
});
