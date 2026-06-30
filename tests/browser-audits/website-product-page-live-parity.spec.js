import { test, expect } from '@playwright/test';

async function expectPageHealthy(page) {
  await expect(page.locator('body')).not.toContainText(/Server Error|Exception|SQLSTATE|Vite manifest|Undefined variable|ReferenceError|TypeError/i);
}

async function loginTenant(page) {
  await page.goto('http://tenant1.localhost:8000/login');
  await expectPageHealthy(page);

  await page.locator('#email').fill('admin@example.com');
  await page.locator('#password').fill('password123');
  await page.getByRole('button', { name: /^log in$/i }).click();

  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  if (page.url().includes('/login')) {
    const bodyText = await page.locator('body').innerText();
    throw new Error(`Tenant login failed. Body: ${bodyText.slice(0, 500)}`);
  }
}

test('tenant live product page renders product page theme sections after reset and publish', async ({ page }) => {
  await loginTenant(page);

  await page.goto('http://tenant1.localhost:8000/manage/product');
  await expectPageHealthy(page);

  const productPageEditorLink = page.locator('[data-product-page-editor-link]').first();

  if (!(await productPageEditorLink.count())) {
    test.skip(true, 'No tenant products available.');
  }

  await productPageEditorLink.click();
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  const currentUrl = page.url();
  const productIdMatch = currentUrl.match(/\/manage\/website\/products\/([^/]+)\/editor/);
  expect(productIdMatch, `Expected product editor URL, received ${currentUrl}`).not.toBeNull();

  const productId = productIdMatch[1];

  await expect(page.locator('body')).toContainText(/Product Details|Product Description|Product Reviews/i);

  await page.getByRole('button', { name: /^reset$/i }).click();
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  await expect(page.locator('body')).toContainText(/Product Details/i);
  await expect(page.locator('body')).toContainText(/Product Description|Description/i);
  await expect(page.locator('body')).toContainText(/Product Reviews|Reviews/i);
  await expect(page.locator('body')).toContainText(/Featured Products|You may also like|Featured products/i);

  await page.getByRole('button', { name: /save draft/i }).click();
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  await page.getByRole('button', { name: /^publish$/i }).click();
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  await page.goto(`http://tenant1.localhost:8000/products/${productId}`);
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  await expect(page.locator('[data-product-page-sections], [data-s69-product-page-theme]').first()).toBeAttached();

  await expect(page.locator('body')).toContainText(/Add to Cart|Buy now|SKU|In Stock|Out of Stock/i);
  await expect(page.locator('body')).toContainText(/Description|Latest smartphone|Product description/i);
  await expect(page.locator('body')).toContainText(/Product Reviews|Reviews|Reviews are coming soon/i);
  await expect(page.locator('body')).toContainText(/You may also like|Featured products|Related products/i);
});
