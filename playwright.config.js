import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/browser-audits',
  timeout: 60000,
  expect: {
    timeout: 10000,
  },
  fullyParallel: false,
  workers: 1,
  reporter: [
    ['list'],
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
    ['json', { outputFile: 'playwright-audit-output.json' }],
  ],
  use: {
    browserName: 'chromium',
    headless: true,
    baseURL: 'http://localhost:8000',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
});
