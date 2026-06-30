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

test('tenant admin can edit publish and view a product page theme draft', async ({ page }) => {
  await loginTenant(page);

  await page.goto('http://tenant1.localhost:8000/manage/product');
  await expectPageHealthy(page);

  await expect(page.locator('body')).toContainText(/Products|No products found/i);

  const productPageEditorLink = page.locator('[data-product-page-editor-link]').first();

  if (!(await productPageEditorLink.count())) {
    test.skip(true, 'No product page editor link exists because there are no tenant products.');
  }

  await productPageEditorLink.click();
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  await expect(page.locator('body')).toContainText(/Website Editor/i);
  await expect(page.locator('body')).toContainText(/Product Details|Product Description|Product Reviews/i);

  const productDetailsSection = page.getByText(/Product Details/i).first();
  await expect(productDetailsSection).toBeVisible();

  await productDetailsSection.click();

  const showBuyNowControl = page.locator('[data-setting-id="show_buy_now"] input[type="checkbox"]').first();
  if (await showBuyNowControl.count()) {
    await showBuyNowControl.setChecked(false);
  }

  await page.getByRole('button', { name: /save draft/i }).click();
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);
  await expect(page.locator('body')).toContainText(/Saved|draft saved|Homepage draft saved or published/i);

  await page.getByRole('button', { name: /^publish$/i }).click();
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);
  await expect(page.locator('body')).toContainText(/Published|Homepage draft saved or published/i);

  const currentUrl = page.url();
  const productIdMatch = currentUrl.match(/\/manage\/website\/products\/([^/]+)\/editor/);
  expect(productIdMatch, `Expected product editor URL, received ${currentUrl}`).not.toBeNull();

  await page.goto(`http://tenant1.localhost:8000/products/${productIdMatch[1]}`);
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  await expect(page.locator('body')).toContainText(/Add to Cart|Product Reviews|Reviews|SKU|In Stock|Out of Stock/i);
  await expect(page.locator('[data-product-page-sections], [data-s69-product-page-theme]').first()).toBeAttached();
});
