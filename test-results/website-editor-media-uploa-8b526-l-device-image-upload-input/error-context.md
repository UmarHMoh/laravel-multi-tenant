# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: website-editor-media-upload.spec.js >> tenant website editor exposes real device image upload input
- Location: tests/browser-audits/website-editor-media-upload.spec.js:13:1

# Error details

```
Error: Channel closed
```

```
Error: locator.click: Target page, context or browser has been closed
Call log:
  - waiting for getByRole('button', { name: /^Add Hero Banner$/ }).first()

```

```
Error: apiRequestContext._wrapApiCall: Target page, context or browser has been closed
```