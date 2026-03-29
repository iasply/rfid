import {expect, test} from '@playwright/test';
import {LoginPage} from '../src/pages/LoginPage';
import {WorkstationPage} from '../src/pages/WorkstationPage';

test.describe('Workstations Module', () => {
    let loginPage: LoginPage;
    let wsPage: WorkstationPage;

    test.beforeEach(async ({page}) => {
        loginPage = new LoginPage(page);
        wsPage = new WorkstationPage(page);

        await loginPage.goto();
        await loginPage.login('admin@cattlerfid.com', 'admin123');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await wsPage.goto();
    });

    test('should list existing workstations', async () => {
        await expect(wsPage.workstationRows.first()).toBeVisible();
    });

    test('should create a new workstation successfully', async ({page}) => {
        const wsDesc = `Estação Teste ${Date.now()}`;
        await wsPage.createWorkstation(wsDesc);

        await expect(page).toHaveURL(/\/admin\/workstations/);
        await expect(page.locator('body')).toContainText('sucesso');
        await expect(page.locator('body')).toContainText(wsDesc);
    });
});
