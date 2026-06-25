# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: website-builder-section-settings.spec.js >> tenant website builder can edit selected section settings and save draft
- Location: tests/browser-audits/website-builder-section-settings.spec.js:19:1

# Error details

```
Test timeout of 60000ms exceeded.
```

```
Error: locator.click: Test timeout of 60000ms exceeded.
Call log:
  - waiting for getByRole('button', { name: /Add Hero Banner/i })

```

# Page snapshot

```yaml
- generic [ref=e3]:
  - banner [ref=e4]:
    - generic [ref=e5]:
      - generic [ref=e6]:
        - link "Back to pages" [ref=e7] [cursor=pointer]:
          - /url: /manage/website
        - generic [ref=e8]:
          - paragraph [ref=e9]: Website Editor
          - heading "Homepage" [level=1] [ref=e10]
        - button "Select page" [ref=e11] [cursor=pointer]
        - generic [ref=e12]: Published
        - generic [ref=e13]: "Published: Yes"
        - generic [ref=e14]: Saved
      - generic [ref=e15]:
        - button "Hide left" [ref=e16] [cursor=pointer]
        - button "Hide right" [ref=e17] [cursor=pointer]
        - generic [ref=e18]:
          - button "Desktop" [ref=e19] [cursor=pointer]
          - button "Tablet" [ref=e20] [cursor=pointer]
          - button "Mobile" [ref=e21] [cursor=pointer]
        - button "Reset" [ref=e22] [cursor=pointer]
        - button "Save draft" [ref=e23] [cursor=pointer]
        - button "Publish" [ref=e24] [cursor=pointer]
  - main [ref=e25]:
    - complementary [ref=e26]:
      - generic [ref=e27]:
        - paragraph [ref=e28]: Left sidebar
        - heading "Pages and structure" [level=2] [ref=e29]
        - generic [ref=e30]:
          - paragraph [ref=e31]: "Sections: 3"
          - paragraph [ref=e32]: "Visible: 2"
        - generic [ref=e33]:
          - button "Header · locked" [ref=e34] [cursor=pointer]
          - button "Homepage" [ref=e35] [cursor=pointer]
          - button "Footer · locked" [ref=e36] [cursor=pointer]
        - generic [ref=e37]:
          - generic [ref=e38]:
            - 'button "1. Rich Text Type: rich_text Blocks: 0 Heading: S41 Browser Published Heading Visible" [ref=e39] [cursor=pointer]':
              - paragraph [ref=e40]: 1. Rich Text
              - paragraph [ref=e41]: "Type: rich_text"
              - paragraph [ref=e42]: "Blocks: 0"
              - paragraph [ref=e43]: "Heading: S41 Browser Published Heading"
              - paragraph [ref=e44]: Visible
            - generic [ref=e45]:
              - button "Duplicate" [ref=e46] [cursor=pointer]
              - button "Move up" [disabled] [ref=e47]
              - button "Move down" [ref=e48] [cursor=pointer]
              - button "Hide section" [ref=e49] [cursor=pointer]
              - button "Remove" [ref=e50] [cursor=pointer]
          - generic [ref=e51]:
            - 'button "2. Rich Text Type: rich_text Blocks: 1 Heading: Tell your story Hidden" [ref=e52] [cursor=pointer]':
              - paragraph [ref=e53]: 2. Rich Text
              - paragraph [ref=e54]: "Type: rich_text"
              - paragraph [ref=e55]: "Blocks: 1"
              - paragraph [ref=e56]: "Heading: Tell your story"
              - paragraph [ref=e57]: Hidden
            - 'button "Feature CardVisible Heading: S40 Browser Feature Card" [ref=e59] [cursor=pointer]':
              - text: Feature CardVisible
              - generic [ref=e60]: "Heading: S40 Browser Feature Card"
            - generic [ref=e61]:
              - button "Duplicate" [ref=e62] [cursor=pointer]
              - button "Move up" [ref=e63] [cursor=pointer]
              - button "Move down" [ref=e64] [cursor=pointer]
              - button "Show section" [ref=e65] [cursor=pointer]
              - button "Remove" [ref=e66] [cursor=pointer]
          - generic [ref=e67]:
            - 'button "3. Rich Text Type: rich_text Blocks: 0 Heading: Tell your story Visible" [ref=e68] [cursor=pointer]':
              - paragraph [ref=e69]: 3. Rich Text
              - paragraph [ref=e70]: "Type: rich_text"
              - paragraph [ref=e71]: "Blocks: 0"
              - paragraph [ref=e72]: "Heading: Tell your story"
              - paragraph [ref=e73]: Visible
            - generic [ref=e74]:
              - button "Duplicate" [ref=e75] [cursor=pointer]
              - button "Move up" [ref=e76] [cursor=pointer]
              - button "Move down" [disabled] [ref=e77]
              - button "Hide section" [ref=e78] [cursor=pointer]
              - button "Remove" [ref=e79] [cursor=pointer]
        - generic [ref=e80]:
          - paragraph [ref=e81]: Linked product preview
          - paragraph [ref=e82]: Product cards automatically link to product detail pages.
          - generic [ref=e83]:
            - link "Smartphone X TTD 699.99" [ref=e84] [cursor=pointer]:
              - /url: /products/smartphone-x
              - generic [ref=e85]: Smartphone X
              - generic [ref=e86]: TTD 699.99
            - link "Laptop Pro TTD 1,299.99" [ref=e87] [cursor=pointer]:
              - /url: /products/laptop-pro
              - generic [ref=e88]: Laptop Pro
              - generic [ref=e89]: TTD 1,299.99
            - link "Men's T-shirt TTD 19.99" [ref=e90] [cursor=pointer]:
              - /url: /products/mens-tshirt
              - generic [ref=e91]: Men's T-shirt
              - generic [ref=e92]: TTD 19.99
            - link "Women's Jeans TTD 49.99" [ref=e93] [cursor=pointer]:
              - /url: /products/womens-jeans
              - generic [ref=e94]: Women's Jeans
              - generic [ref=e95]: TTD 49.99
        - generic [ref=e96]:
          - generic [ref=e97]: Favorite sections Pin quick sections Pin sections you use often for quick access. Add pinned Hero Banner Section presets Add ready-made section layouts with starter content. Add preset Hero with CTA No matching sections found. Try a shorter word. No matching sections found for this category or search. Section categories Upload image from device
          - heading "Available sections" [level=2] [ref=e98]
          - searchbox "Search sections" [ref=e99]
          - generic [ref=e100]:
            - button "All (6)" [ref=e101] [cursor=pointer]
            - button "General (1)" [ref=e102] [cursor=pointer]
            - button "Text (1)" [ref=e103] [cursor=pointer]
            - button "Products (2)" [ref=e104] [cursor=pointer]
            - button "Customer (2)" [ref=e105] [cursor=pointer]
          - generic [ref=e106]:
            - paragraph [ref=e107]: Favorite sections
            - paragraph [ref=e108]: Pin sections you use often for quick access.
            - generic [ref=e109]:
              - button "Pin" [ref=e110] [cursor=pointer]
              - button "Pin Rich Text" [ref=e111] [cursor=pointer]
              - button "Pin Featured Products" [ref=e112] [cursor=pointer]
              - button "Pin Product Grid" [ref=e113] [cursor=pointer]
              - button "Pin Reviews & Comments" [ref=e114] [cursor=pointer]
              - button "Pin Contact Form" [ref=e115] [cursor=pointer]
          - generic [ref=e116]:
            - paragraph [ref=e117]: Section presets
            - paragraph [ref=e118]: Add ready-made section layouts with starter content.
            - generic [ref=e119]:
              - button "Add preset Hero with CTA" [ref=e120] [cursor=pointer]
              - button "Add preset Hero Minimal" [ref=e121] [cursor=pointer]
              - button "Add preset Product Feature" [ref=e122] [cursor=pointer]
              - button "Add preset Text Block" [ref=e123] [cursor=pointer]
          - generic [ref=e124]:
            - generic [ref=e125]:
              - paragraph
              - paragraph [ref=e126]: · 0 setting(s) · 0 block type(s)
              - button "Add" [ref=e127] [cursor=pointer]
            - generic [ref=e128]:
              - paragraph [ref=e129]: Rich Text
              - paragraph [ref=e130]: Text · 3 setting(s) · 1 block type(s)
              - button "Add Rich Text" [ref=e131] [cursor=pointer]
            - generic [ref=e132]:
              - paragraph [ref=e133]: Featured Products
              - paragraph [ref=e134]: Products · 2 setting(s) · 1 block type(s)
              - button "Add Featured Products" [ref=e135] [cursor=pointer]
            - generic [ref=e136]:
              - paragraph [ref=e137]: Product Grid
              - paragraph [ref=e138]: Products · 9 setting(s) · 1 block type(s)
              - button "Add Product Grid" [ref=e139] [cursor=pointer]
            - generic [ref=e140]:
              - paragraph [ref=e141]: Reviews & Comments
              - paragraph [ref=e142]: Customer · 2 setting(s) · 0 block type(s)
              - button "Add Reviews & Comments" [ref=e143] [cursor=pointer]
            - generic [ref=e144]:
              - paragraph [ref=e145]: Contact Form
              - paragraph [ref=e146]: Customer · 3 setting(s) · 0 block type(s)
              - button "Add Contact Form" [ref=e147] [cursor=pointer]
    - generic [ref=e149]:
      - generic [ref=e150]:
        - paragraph [ref=e151]: Live preview
        - paragraph [ref=e152]: Storefront preview
        - paragraph [ref=e153]: desktop view
        - paragraph [ref=e154]: "Selected: Homepage > Rich Text"
      - generic [ref=e156]:
        - generic [ref=e157]:
          - paragraph [ref=e158]: Header · always present
          - generic [ref=e159]:
            - paragraph [ref=e160]: Aromniac
            - navigation [ref=e161]:
              - link "Shop" [ref=e162] [cursor=pointer]:
                - /url: /home
              - link "Contact" [ref=e163] [cursor=pointer]:
                - /url: /pages/contact
              - link "Cart" [ref=e164] [cursor=pointer]:
                - /url: /cart
        - generic [ref=e165]:
          - generic [ref=e166] [cursor=pointer]:
            - heading "S41 Browser Published Heading" [level=2] [ref=e167]
            - paragraph [ref=e168]: Share information about your brand, products, or mission.
          - generic [ref=e169] [cursor=pointer]:
            - heading "Tell your story" [level=2] [ref=e170]
            - paragraph [ref=e171]: Share information about your brand, products, or mission.
        - generic [ref=e172]:
          - paragraph [ref=e173]: Footer · always present
          - paragraph [ref=e174]: Powered by your Hasan Marketing.
          - navigation [ref=e175]:
            - link "Shop" [ref=e176] [cursor=pointer]:
              - /url: /home
            - link "Contact" [ref=e177] [cursor=pointer]:
              - /url: /pages/contact
    - complementary [ref=e178]:
      - generic [ref=e179]:
        - paragraph [ref=e180]: Right sidebar
        - heading "Homepage > Rich Text" [level=2] [ref=e181]
        - paragraph [ref=e182]: Section settings
        - paragraph [ref=e183]: "Page settings Section settings Block settings Type: Homepage"
        - generic [ref=e184]:
          - generic [ref=e185]:
            - paragraph [ref=e186]: Selected section
            - paragraph [ref=e187]: Rich Text
            - paragraph [ref=e188]: "Type: rich_text"
          - generic [ref=e189]:
            - generic [ref=e190]: Heading
            - textbox "Heading" [ref=e191]: S41 Browser Published Heading
          - generic [ref=e192]:
            - generic [ref=e193]: Text
            - textbox "Text" [ref=e194]: Share information about your brand, products, or mission.
          - generic [ref=e195]:
            - generic [ref=e196]: Width
            - combobox "Width" [ref=e197]:
              - option "Narrow"
              - option "Normal" [selected]
              - option "Wide"
          - generic [ref=e198]:
            - paragraph [ref=e199]: Blocks inside section
            - button "Add Feature Card" [ref=e201] [cursor=pointer]
  - generic [ref=e202]: "S43 foundation S44 selection model S45 preview rendering polish S46 link picker foundation S47 image picker foundation S48 reset draft foundation S49 dirty state foundation S50 remove safety foundation S51 duplicate foundation S52 section search foundation S53 section categories grouping foundation S54 section favorites S55 section templates presets S56 rendering parity product cards foundation S57 media upload foundation S58 storefront editor parity S59 storefront visual parity S60 storefront product card polish S61 storefront product image data S62 storefront real product grid S63 clean builder architecture setting.type === 'textarea' setting.type === 'number' setting.type === 'select' setting.type === 'checkbox' data-setting-id data-block-setting-id Blocks inside section Move block up Move block down Hide block Remove block Duplicate block Custom URL... Pick an internal page or paste a custom URL. Image picker foundation Block image picker foundation Paste image URL for now Clear image Header Footer locked Homepage sections Product cards automatically link to product detail pages data-storefront-builder-homepage Page settings Section settings Block settings Sections: 3 Featured products Click to edit section Storefront preview mobile view Smart search handles apostrophes, plurals, and small spelling mistakes. Hero image preview placeholder Hero image Real product preview Heading: S40 Browser Feature Card"
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | const tenantBase = 'http://tenant1.localhost:8000';
  4  | 
  5  | async function expectHealthy(page) {
  6  |   await page.waitForLoadState('domcontentloaded');
  7  | 
  8  |   const body = page.locator('body');
  9  | 
  10 |   await expect(body).toBeAttached();
  11 |   await expect(body).not.toContainText('Server Error');
  12 |   await expect(body).not.toContainText('Method Not Allowed');
  13 |   await expect(body).not.toContainText('SQLSTATE');
  14 |   await expect(body).not.toContainText('Undefined variable');
  15 |   await expect(body).not.toContainText('Attempt to read property');
  16 |   await expect(body).not.toContainText('Base table or view not found');
  17 | }
  18 | 
  19 | test('tenant website builder can edit selected section settings and save draft', async ({ page }) => {
  20 |   await page.goto(`${tenantBase}/login`);
  21 |   await expectHealthy(page);
  22 | 
  23 |   await page.fill('#email', 'admin@example.com');
  24 |   await page.fill('#password', 'password123');
  25 |   await page.click('button[type="submit"]');
  26 | 
  27 |   await page.waitForURL(/dashboard|manage|home/);
  28 |   await expectHealthy(page);
  29 | 
  30 |   await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  31 |   await expectHealthy(page);
  32 | 
  33 |   await expect(page.locator('body')).toContainText('Section settings');
  34 | 
> 35 |   await page.getByRole('button', { name: /Add Hero Banner/i }).click();
     |                                                                ^ Error: locator.click: Test timeout of 60000ms exceeded.
  36 | 
  37 |   const headingInput = page.locator('[data-setting-id="heading"]').first();
  38 |   await expect(headingInput).toBeVisible();
  39 | 
  40 |   await headingInput.fill('S39 Edited Hero Heading');
  41 | 
  42 |   await page.getByRole('button', { name: /Save draft/i }).click();
  43 |   await expect(page.locator('body')).toContainText(/Homepage draft saved|Homepage draft saved or published|Homepage published/i);
  44 |   await expect(page.locator('body')).toContainText(/S39 Edited Hero Heading|Heading:/);
  45 |   await expectHealthy(page);
  46 | 
  47 |   await page.reload();
  48 |   await expectHealthy(page);
  49 |   await expect(page.locator('body')).toContainText(/S39 Edited Hero Heading|Heading:/);
  50 | });
  51 | 
```