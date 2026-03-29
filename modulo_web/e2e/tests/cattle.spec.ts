import {expect, test} from '@playwright/test';
import {LoginPage} from '../src/pages/LoginPage';
import {CattlePage} from '../src/pages/CattlePage';

test.describe('Cattle Module', () => {
    let loginPage: LoginPage;
    let cattlePage: CattlePage;

    test.beforeEach(async ({page}) => {
        loginPage = new LoginPage(page);
        cattlePage = new CattlePage(page);

        await loginPage.goto();
        await loginPage.login('admin@cattlerfid.com', 'admin123');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await cattlePage.goto();
    });

    test('should list existing cattle', async () => {
        await expect(cattlePage.cattleRows.first()).toBeVisible();
    });

    test('should navigate to create form and back', async ({page}) => {
        await cattlePage.createLink.click();
        await expect(page).toHaveURL(/\/admin\/cattle\/create/);
        await page.goBack();
        await expect(page).toHaveURL(/\/admin\/cattle/);
    });

    test('should create a new cattle successfully', async ({page}) => {
        const animalName = `Mimosa-${Date.now()}`;
        await cattlePage.createCattle(animalName, '450.50');

        await expect(page).toHaveURL(/\/admin\/cattle/);
        await expect(page.locator('body')).toContainText('Animal cadastrado!');
        await expect(page.locator('body')).toContainText(animalName);
    });
});
