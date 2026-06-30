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

async function openFirstProductEditor(page) {
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

  return {
    productId: productIdMatch[1],
  };
}

async function selectProductDetails(page) {
  const buyNowToggle = page.locator('[data-setting-id="show_buy_now"] [data-s78-real-section-checkbox]').first();

  if (await buyNowToggle.count()) {
    await expect(buyNowToggle).toBeVisible();
    return buyNowToggle;
  }

  const productDetailsButtons = page.locator('button').filter({ hasText: /Product Details/i });
  const buttonCount = await productDetailsButtons.count();

  for (let index = 0; index < buttonCount; index += 1) {
    await productDetailsButtons.nth(index).click();
    await page.waitForTimeout(250);

    if (await buyNowToggle.count()) {
      await expect(buyNowToggle).toBeVisible();
      return buyNowToggle;
    }
  }

  const bodyText = await page.locator('body').innerText();
  throw new Error(`Could not expose Product Details show_buy_now setting. Body: ${bodyText.slice(0, 1200)}`);
}

async function waitForEditorRequest(page, method, action) {
  const requestPromise = page.waitForRequest((request) => {
    return request.method() === method
      && request.url().includes('tenant1.localhost:8000/manage/website/pages/');
  });

  await action();

  const request = await requestPromise;
  const response = await request.response();

  expect(response, `${method} editor request did not return a response`).not.toBeNull();
  expect(response.status(), `${method} editor request failed`).toBeLessThan(500);

  await page.waitForTimeout(300);
  await expectPageHealthy(page);
}

async function saveAndPublish(page) {
  await waitForEditorRequest(page, 'PUT', async () => {
    await page.getByRole('button', { name: /save draft/i }).click();
  });

  await waitForEditorRequest(page, 'POST', async () => {
    await page.getByRole('button', { name: /^publish$/i }).click();
  });
}

test('tenant product page buy now setting persists to live storefront', async ({ page }) => {
  await loginTenant(page);

  const editorTarget = await openFirstProductEditor(page);
  const productId = editorTarget.productId;

  await page.getByRole('button', { name: /^reset$/i }).click();
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  let buyNowToggle = await selectProductDetails(page);

  await buyNowToggle.setChecked(false);
  await expect(buyNowToggle).not.toBeChecked();

  await saveAndPublish(page);

  await page.goto(`http://tenant1.localhost:8000/products/${productId}`);
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  const productDetailsSection = page.locator('[data-product-details-section]').first();
  await expect(productDetailsSection).toBeVisible();
  await expect(productDetailsSection).not.toContainText(/Buy now/i);

  await page.goto(`http://tenant1.localhost:8000/manage/website/products/${productId}/editor`);
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  buyNowToggle = await selectProductDetails(page);
  await buyNowToggle.setChecked(true);
  await expect(buyNowToggle).toBeChecked();

  await saveAndPublish(page);

  await page.goto(`http://tenant1.localhost:8000/products/${productId}`);
  await page.waitForLoadState('networkidle');
  await expectPageHealthy(page);

  await expect(page.locator('[data-product-details-section]').first()).toContainText(/Buy now/i);
});
