import {expect, test} from '@playwright/test';
import {LoginPage} from '../../src/pages/LoginPage';

test.describe('Login Flow', () => {
    let loginPage: LoginPage;

    test.beforeEach(async ({page}) => {
        loginPage = new LoginPage(page);
        await loginPage.goto();
    });

    test('should login successfully with valid credentials', async ({page}) => {
        await loginPage.login('admin@cattlerfid.com', 'admin123');

        // After login, we expect to be redirected to the dashboard
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        // Check for some element that confirms we are on the dashboard
        await expect(page.locator('body')).toContainText('Painel de Controle');
    });

    test('should logout and allow login again without 419', async ({page}) => {
        await loginPage.login('admin@cattlerfid.com', 'admin123');
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        // Click the sidebar logout button (first "Sair do Sistema" on desktop)
        await page.getByRole('button', {name: 'Sair do Sistema'}).first().click();

        // Should redirect back to login
        await expect(page).toHaveURL(/\/login/);
        await expect(loginPage.submitButton).toBeVisible();

        // Login again — must not hit 419
        await loginPage.login('admin@cattlerfid.com', 'admin123');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await expect(page.locator('body')).toContainText('Painel de Controle');
    });

    test('should redirect to dashboard when already logged in navigates to /login', async ({page}) => {
        await loginPage.login('admin@cattlerfid.com', 'admin123');
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        // Navigate directly to /login while already authenticated
        await page.goto('/login');

        // Must redirect to dashboard — no 419, no login form
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await expect(page.locator('body')).toContainText('Painel de Controle');
    });

    test('should show error with invalid credentials', async ({page}) => {
        const email = `invalid_${test.info().project.name}_${test.info().parallelIndex}@email.com`;
        await loginPage.login(email, 'wrongpassword');

        await expect(loginPage.errorMessage).toBeVisible();
        await expect(loginPage.errorMessage).toContainText('records');
    });

    test('should rate limit after 5 failed attempts', async ({page}) => {
        // Use a unique email per browser project and worker to avoid parallel collision
        const email = `throttle_test_${test.info().project.name}_${test.info().parallelIndex}@email.com`;

        for (let i = 0; i < 5; i++) {
            await loginPage.login(email, 'wrongpass');
            // toHaveText is more robust than toBeVisible as it waits for stable content
            await expect(loginPage.errorMessage).toHaveText(/match/i);
        }

        // 6th attempt should be blocked by rate limiter
        await loginPage.login(email, 'wrongpass');
        await expect(loginPage.errorMessage).toHaveText(/(too many|muitas tentativas|segundos|seconds|attempts|bloqueado)/i, {timeout: 10000});
    });
});

