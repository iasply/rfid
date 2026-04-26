import {expect, test} from '@playwright/test';
import {LoginPage} from '../src/pages/LoginPage';
import {VaccinePage} from '../src/pages/VaccinePage';

test.describe('Vaccines Module', () => {
    let loginPage: LoginPage;
    let vaccinePage: VaccinePage;

    test.beforeEach(async ({page}) => {
        loginPage = new LoginPage(page);
        vaccinePage = new VaccinePage(page);

        await loginPage.goto();
        await loginPage.login('admin@cattlerfid.com', 'admin123');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await vaccinePage.goto();
    });

    test('should list existing vaccine records', async () => {
        await expect(vaccinePage.vaccineRows.first()).toBeVisible();
    });
});
