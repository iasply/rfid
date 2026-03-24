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
        await expect(page.locator('body')).toContainText('Resumo do Sistema');
    });

    test('should show error with invalid credentials', async ({page}) => {
        await loginPage.login('wrong@email.com', 'wrongpassword');

        await expect(loginPage.errorMessage).toBeVisible();
        await expect(loginPage.errorMessage).toContainText('records');
    });

    test('should rate limit after 5 failed attempts', async ({page}) => {
        // Use a specific email to not conflict with parallel tests using the same IP
        const email = 'throttle_test@email.com';

        for (let i = 0; i < 5; i++) {
            await loginPage.login(email, 'wrongpass');
            await expect(loginPage.errorMessage).toBeVisible();
        }

        // 6th attempt should be blocked by rate limiter
        await loginPage.login(email, 'wrongpass');
        await expect(loginPage.errorMessage).toBeVisible();
        
        const errorText = await loginPage.errorMessage.textContent();
        expect(errorText?.toLowerCase()).toMatch(/(too many|muitas tentativas|segundos|seconds)/i);
    });
});

