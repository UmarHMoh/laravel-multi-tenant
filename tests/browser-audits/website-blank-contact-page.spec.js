import { test, expect } from '@playwright/test';

const tenantBase = 'http://tenant1.localhost:8000';

async function login(page) {
  await page.goto(`${tenantBase}/login`);
  await page.getByLabel(/email/i).fill('admin@example.com');
  await page.getByLabel(/password/i).fill('password123');
  await page.getByRole('button', { name: /log in|login/i }).click();
  await page.waitForLoadState('networkidle').catch(() => null);
}

test('tenant storefront supports blank homepage and contact page foundation', async ({ page }) => {
  await page.goto(`${tenantBase}/home`, { waitUntil: 'domcontentloaded' });
  await expect(page.locator('body')).toContainText(/Storefront|Shop|Cart/);

  await page.goto(`${tenantBase}/contact`, { waitUntil: 'domcontentloaded' });
  await expect(page.locator('body')).toContainText(/Contact us|Send us a message|Send message/);
  await expect(page.locator('[data-contact-form-foundation]').first()).toBeVisible();
});

test('tenant website editor can add contact form section', async ({ page }) => {
  await login(page);
  await page.goto(`${tenantBase}/manage/website/homepage/editor`, { waitUntil: 'domcontentloaded' });

  await expect(page.locator('body')).toContainText('Website Editor');
  await expect(page.getByRole('button', { name: /^Add Contact Form$/ }).first()).toBeVisible();

  await page.getByRole('button', { name: /^Add Contact Form$/ }).first().click();
  await expect(page.locator('body')).toContainText(/Contact form|Contact us|Send message/);
});
