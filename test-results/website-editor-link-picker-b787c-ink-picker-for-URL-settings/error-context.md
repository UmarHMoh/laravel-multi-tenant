# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: website-editor-link-picker.spec.js >> tenant website editor exposes link picker for URL settings
- Location: tests/browser-audits/website-editor-link-picker.spec.js:13:1

# Error details

```
Test timeout of 60000ms exceeded.
```

```
Error: locator.click: Test timeout of 60000ms exceeded.
Call log:
  - waiting for getByRole('button', { name: /^Add Hero Banner$/ }).first()

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
          - paragraph [ref=e31]: "Sections: 4"
          - paragraph [ref=e32]: "Visible: 3"
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
              - button "Move down" [ref=e77] [cursor=pointer]
              - button "Hide section" [ref=e78] [cursor=pointer]
              - button "Remove" [ref=e79] [cursor=pointer]
          - generic [ref=e80]:
            - 'button "4. Rich Text Type: rich_text Blocks: 0 Heading: Tell your story Visible" [ref=e81] [cursor=pointer]':
              - paragraph [ref=e82]: 4. Rich Text
              - paragraph [ref=e83]: "Type: rich_text"
              - paragraph [ref=e84]: "Blocks: 0"
              - paragraph [ref=e85]: "Heading: Tell your story"
              - paragraph [ref=e86]: Visible
            - generic [ref=e87]:
              - button "Duplicate" [ref=e88] [cursor=pointer]
              - button "Move up" [ref=e89] [cursor=pointer]
              - button "Move down" [disabled] [ref=e90]
              - button "Hide section" [ref=e91] [cursor=pointer]
              - button "Remove" [ref=e92] [cursor=pointer]
        - generic [ref=e93]:
          - paragraph [ref=e94]: Linked product preview
          - paragraph [ref=e95]: Product cards automatically link to product detail pages.
          - generic [ref=e96]:
            - link "Smartphone X TTD 699.99" [ref=e97] [cursor=pointer]:
              - /url: /products/smartphone-x
              - generic [ref=e98]: Smartphone X
              - generic [ref=e99]: TTD 699.99
            - link "Laptop Pro TTD 1,299.99" [ref=e100] [cursor=pointer]:
              - /url: /products/laptop-pro
              - generic [ref=e101]: Laptop Pro
              - generic [ref=e102]: TTD 1,299.99
            - link "Men's T-shirt TTD 19.99" [ref=e103] [cursor=pointer]:
              - /url: /products/mens-tshirt
              - generic [ref=e104]: Men's T-shirt
              - generic [ref=e105]: TTD 19.99
            - link "Women's Jeans TTD 49.99" [ref=e106] [cursor=pointer]:
              - /url: /products/womens-jeans
              - generic [ref=e107]: Women's Jeans
              - generic [ref=e108]: TTD 49.99
        - generic [ref=e109]:
          - generic [ref=e110]: Favorite sections Pin quick sections Pin sections you use often for quick access. Add pinned Hero Banner Section presets Add ready-made section layouts with starter content. Add preset Hero with CTA No matching sections found. Try a shorter word. No matching sections found for this category or search. Section categories Upload image from device
          - heading "Available sections" [level=2] [ref=e111]
          - searchbox "Search sections" [ref=e112]
          - generic [ref=e113]:
            - button "All (6)" [ref=e114] [cursor=pointer]
            - button "General (1)" [ref=e115] [cursor=pointer]
            - button "Text (1)" [ref=e116] [cursor=pointer]
            - button "Products (2)" [ref=e117] [cursor=pointer]
            - button "Customer (2)" [ref=e118] [cursor=pointer]
          - generic [ref=e119]:
            - paragraph [ref=e120]: Favorite sections
            - paragraph [ref=e121]: Pin sections you use often for quick access.
            - generic [ref=e122]:
              - button "Pin" [ref=e123] [cursor=pointer]
              - button "Pin Rich Text" [ref=e124] [cursor=pointer]
              - button "Pin Featured Products" [ref=e125] [cursor=pointer]
              - button "Pin Product Grid" [ref=e126] [cursor=pointer]
              - button "Pin Reviews & Comments" [ref=e127] [cursor=pointer]
              - button "Pin Contact Form" [ref=e128] [cursor=pointer]
          - generic [ref=e129]:
            - paragraph [ref=e130]: Section presets
            - paragraph [ref=e131]: Add ready-made section layouts with starter content.
            - generic [ref=e132]:
              - button "Add preset Hero with CTA" [ref=e133] [cursor=pointer]
              - button "Add preset Hero Minimal" [ref=e134] [cursor=pointer]
              - button "Add preset Product Feature" [ref=e135] [cursor=pointer]
              - button "Add preset Text Block" [ref=e136] [cursor=pointer]
          - generic [ref=e137]:
            - generic [ref=e138]:
              - paragraph
              - paragraph [ref=e139]: · 0 setting(s) · 0 block type(s)
              - button "Add" [ref=e140] [cursor=pointer]
            - generic [ref=e141]:
              - paragraph [ref=e142]: Rich Text
              - paragraph [ref=e143]: Text · 3 setting(s) · 1 block type(s)
              - button "Add Rich Text" [ref=e144] [cursor=pointer]
            - generic [ref=e145]:
              - paragraph [ref=e146]: Featured Products
              - paragraph [ref=e147]: Products · 2 setting(s) · 1 block type(s)
              - button "Add Featured Products" [ref=e148] [cursor=pointer]
            - generic [ref=e149]:
              - paragraph [ref=e150]: Product Grid
              - paragraph [ref=e151]: Products · 9 setting(s) · 1 block type(s)
              - button "Add Product Grid" [ref=e152] [cursor=pointer]
            - generic [ref=e153]:
              - paragraph [ref=e154]: Reviews & Comments
              - paragraph [ref=e155]: Customer · 2 setting(s) · 0 block type(s)
              - button "Add Reviews & Comments" [ref=e156] [cursor=pointer]
            - generic [ref=e157]:
              - paragraph [ref=e158]: Contact Form
              - paragraph [ref=e159]: Customer · 3 setting(s) · 0 block type(s)
              - button "Add Contact Form" [ref=e160] [cursor=pointer]
    - generic [ref=e162]:
      - generic [ref=e163]:
        - paragraph [ref=e164]: Live preview
        - paragraph [ref=e165]: Storefront preview
        - paragraph [ref=e166]: desktop view
        - paragraph [ref=e167]: "Selected: Homepage > Rich Text"
      - generic [ref=e169]:
        - generic [ref=e170]:
          - paragraph [ref=e171]: Header · always present
          - generic [ref=e172]:
            - paragraph [ref=e173]: Aromniac
            - navigation [ref=e174]:
              - link "Shop" [ref=e175] [cursor=pointer]:
                - /url: /home
              - link "Contact" [ref=e176] [cursor=pointer]:
                - /url: /pages/contact
              - link "Cart" [ref=e177] [cursor=pointer]:
                - /url: /cart
        - generic [ref=e178]:
          - generic [ref=e179] [cursor=pointer]:
            - heading "S41 Browser Published Heading" [level=2] [ref=e180]
            - paragraph [ref=e181]: Share information about your brand, products, or mission.
          - generic [ref=e182] [cursor=pointer]:
            - heading "Tell your story" [level=2] [ref=e183]
            - paragraph [ref=e184]: Share information about your brand, products, or mission.
          - generic [ref=e185] [cursor=pointer]:
            - heading "Tell your story" [level=2] [ref=e186]
            - paragraph [ref=e187]: Share information about your brand, products, or mission.
        - generic [ref=e188]:
          - paragraph [ref=e189]: Footer · always present
          - paragraph [ref=e190]: Powered by your Hasan Marketing.
          - navigation [ref=e191]:
            - link "Shop" [ref=e192] [cursor=pointer]:
              - /url: /home
            - link "Contact" [ref=e193] [cursor=pointer]:
              - /url: /pages/contact
    - complementary [ref=e194]:
      - generic [ref=e195]:
        - paragraph [ref=e196]: Right sidebar
        - heading "Homepage > Rich Text" [level=2] [ref=e197]
        - paragraph [ref=e198]: Section settings
        - paragraph [ref=e199]: "Page settings Section settings Block settings Type: Homepage"
        - generic [ref=e200]:
          - generic [ref=e201]:
            - paragraph [ref=e202]: Selected section
            - paragraph [ref=e203]: Rich Text
            - paragraph [ref=e204]: "Type: rich_text"
          - generic [ref=e205]:
            - generic [ref=e206]: Heading
            - textbox "Heading" [ref=e207]: S41 Browser Published Heading
          - generic [ref=e208]:
            - generic [ref=e209]: Text
            - textbox "Text" [ref=e210]: Share information about your brand, products, or mission.
          - generic [ref=e211]:
            - generic [ref=e212]: Width
            - combobox "Width" [ref=e213]:
              - option "Narrow"
              - option "Normal" [selected]
              - option "Wide"
          - generic [ref=e214]:
            - paragraph [ref=e215]: Blocks inside section
            - button "Add Feature Card" [ref=e217] [cursor=pointer]
  - generic [ref=e218]: "S43 foundation S44 selection model S45 preview rendering polish S46 link picker foundation S47 image picker foundation S48 reset draft foundation S49 dirty state foundation S50 remove safety foundation S51 duplicate foundation S52 section search foundation S53 section categories grouping foundation S54 section favorites S55 section templates presets S56 rendering parity product cards foundation S57 media upload foundation S58 storefront editor parity S59 storefront visual parity S60 storefront product card polish S61 storefront product image data S62 storefront real product grid S63 clean builder architecture setting.type === 'textarea' setting.type === 'number' setting.type === 'select' setting.type === 'checkbox' data-setting-id data-block-setting-id Blocks inside section Move block up Move block down Hide block Remove block Duplicate block Custom URL... Pick an internal page or paste a custom URL. Image picker foundation Block image picker foundation Paste image URL for now Clear image Header Footer locked Homepage sections Product cards automatically link to product detail pages data-storefront-builder-homepage Page settings Section settings Block settings Sections: 3 Featured products Click to edit section Storefront preview mobile view Smart search handles apostrophes, plurals, and small spelling mistakes. Hero image preview placeholder Hero image Real product preview Heading: S40 Browser Feature Card"
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | const tenantBase = 'http://tenant1.localhost:8000';
  4  | 
  5  | async function login(page) {
  6  |   await page.goto(`${tenantBase}/login`);
  7  |   await page.getByLabel(/email/i).fill('admin@example.com');
  8  |   await page.getByLabel(/password/i).fill('password123');
  9  |   await page.getByRole('button', { name: /log in|login/i }).click();
  10 |   await page.waitForLoadState('networkidle').catch(() => null);
  11 | }
  12 | 
  13 | test('tenant website editor exposes link picker for URL settings', async ({ page }) => {
  14 |   await login(page);
  15 |   await page.goto(`${tenantBase}/manage/website/homepage/editor`);
  16 |   await expect(page.locator('body')).toContainText('Website Editor');
  17 | 
> 18 |   await page.getByRole('button', { name: /^Add Hero Banner$/ }).first().click();
     |                                                                         ^ Error: locator.click: Test timeout of 60000ms exceeded.
  19 |   await expect(page.locator('body')).toContainText(/CTA button link|Custom URL|Home page|Pick an internal page or paste a custom URL/);
  20 | });
  21 | 
```