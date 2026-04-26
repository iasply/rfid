import {expect, test} from '@playwright/test';
import {LoginPage} from '../src/pages/LoginPage';
import {VaccineTypePage} from '../src/pages/VaccineTypePage';

test.describe('Vaccine Types Module', () => {
    let loginPage: LoginPage;
    let vaccineTypePage: VaccineTypePage;

    test.beforeEach(async ({page}) => {
        loginPage = new LoginPage(page);
        vaccineTypePage = new VaccineTypePage(page);

        await loginPage.goto();
        await loginPage.login('admin@cattlerfid.com', 'admin123');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await vaccineTypePage.goto();
    });

    test('should list existing vaccine types', async () => {
        await expect(vaccineTypePage.vaccineTypeRows.first()).toBeVisible();
    });

    test('should navigate to create form and back', async ({page}) => {
        await vaccineTypePage.createLink.click();
        await expect(page).toHaveURL(/\/admin\/vaccine-types\/create/);
        await page.goBack();
        await expect(page).toHaveURL(/\/admin\/vaccine-types/);
    });

    test('should create a new vaccine type successfully', async ({page}) => {
        const typeName = `Teste-${Date.now()}`;
        await vaccineTypePage.createVaccineType(typeName, '90');

        await expect(page).toHaveURL(/\/admin\/vaccine-types/);
        await expect(page.locator('body')).toContainText('Tipo de vacina cadastrado');
        await expect(page.locator('body')).toContainText(typeName);
    });

    test('should show validation error for duplicate name', async ({page}) => {
        await vaccineTypePage.createLink.click();
        await vaccineTypePage.nameInput.fill('Febre Aftosa');
        await vaccineTypePage.submitButton.click();

        await expect(page).toHaveURL(/\/admin\/vaccine-types\/create/);
        await expect(page.locator('body')).toContainText('already been taken');
    });

    test('should edit an existing vaccine type', async ({page}) => {
        await page.getByTestId('vaccine-type-edit-link').first().click();
        await expect(page).toHaveURL(/\/admin\/vaccine-types\/\d+\/edit/);

        await vaccineTypePage.intervalInput.fill('270');
        await vaccineTypePage.submitButton.click();

        await expect(page).toHaveURL(/\/admin\/vaccine-types/);
        await expect(page.locator('body')).toContainText('atualizado');
    });

    test('should show vaccine type statistics page', async ({page}) => {
        await page.getByTestId('vaccine-type-show-link').first().click();
        await expect(page).toHaveURL(/\/admin\/vaccine-types\/\d+$/);

        await expect(page.locator('#chart-vt-monthly')).toBeVisible();
        await expect(page.locator('#chart-vt-coverage')).toBeVisible();
        await expect(page.locator('#chart-vt-weight')).toBeVisible();
    });
});
