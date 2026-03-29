import {expect, test} from '@playwright/test';
import {LoginPage} from '../src/pages/LoginPage';
import {VeterinarianPage} from '../src/pages/VeterinarianPage';

test.describe('Veterinarians Module', () => {
    let loginPage: LoginPage;
    let vetPage: VeterinarianPage;

    test.beforeEach(async ({page}) => {
        loginPage = new LoginPage(page);
        vetPage = new VeterinarianPage(page);

        await loginPage.goto();
        await loginPage.login('admin@cattlerfid.com', 'admin123');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await vetPage.goto();
    });

    test('should list existing veterinarians', async () => {
        await expect(vetPage.vetRows.first()).toBeVisible();
    });

    test('should navigate to create form and back', async ({page}) => {
        await vetPage.createButton.click();
        await expect(page).toHaveURL(/\/admin\/veterinarians\/create/);
        await page.goBack();
        await expect(page).toHaveURL(/\/admin\/veterinarians/);
    });

    test('should create a new veterinarian successfully', async ({page}) => {
        const uniqueEmail = `vet-${Date.now()}@example.com`;
        await vetPage.createVeterinarian('Dr. Test e2e', uniqueEmail, 'password123');

        await expect(page).toHaveURL(/\/admin\/veterinarians/);
        await expect(page.locator('body')).toContainText('sucesso');
        await expect(page.locator('body')).toContainText('Dr. Test e2e');
    });
});
