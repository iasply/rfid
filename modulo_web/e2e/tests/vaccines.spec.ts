import {expect, test} from '@playwright/test';
import {LoginPage} from '../src/pages/LoginPage';
import {VaccinePage} from '../src/pages/VaccinePage';
import {CattlePage} from '../src/pages/CattlePage';

test.describe('Vaccines Module', () => {
    let loginPage: LoginPage;
    let vaccinePage: VaccinePage;
    let cattlePage: CattlePage;

    test.beforeEach(async ({page}) => {
        loginPage = new LoginPage(page);
        vaccinePage = new VaccinePage(page);
        cattlePage = new CattlePage(page);

        await loginPage.goto();
        await loginPage.login('admin@cattlerfid.com', 'admin123');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await vaccinePage.goto();
    });

    test('should list existing vaccine records', async () => {
        await expect(vaccinePage.vaccineRows.first()).toBeVisible();
    });

    test('should navigate to create form and back', async ({page}) => {
        await page.goto('/admin/vaccines/create');
        await expect(vaccinePage.vaccineTypeSelect).toBeVisible();
        await page.goBack();
        await expect(page).toHaveURL(/\/admin\/vaccines/);
    });

    test('should create a new vaccine successfully', async ({page}) => {
        await page.goto('/admin/vaccines/create');

        await vaccinePage.animalSelect.selectOption({index: 1});
        await vaccinePage.vaccineTypeSelect.selectOption({index: 1});
        await vaccinePage.weightInput.fill('350.5');
        await vaccinePage.vetSelect.selectOption({index: 1});
        await vaccinePage.submitButton.click();

        await expect(page).toHaveURL(/\/admin\/vaccines/);
        await expect(page.locator('body')).toContainText('Vacinação registrada');
    });
});
