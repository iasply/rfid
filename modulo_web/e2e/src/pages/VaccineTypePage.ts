import {type Locator, type Page} from '@playwright/test';

export class VaccineTypePage {
    readonly page: Page;
    readonly vaccineTypeRows: Locator;
    readonly createLink: Locator;
    readonly nameInput: Locator;
    readonly intervalInput: Locator;
    readonly submitButton: Locator;

    constructor(page: Page) {
        this.page = page;
        this.vaccineTypeRows = page.getByTestId('vaccine-type-row');
        this.createLink = page.getByTestId('create-vaccine-type-link');
        this.nameInput = page.getByTestId('name');
        this.intervalInput = page.getByTestId('interval_days');
        this.submitButton = page.getByTestId('vaccine-type-submit');
    }

    async goto() {
        await this.page.goto('/admin/vaccine-types');
    }

    async createVaccineType(name: string, intervalDays?: string) {
        await this.createLink.click();
        await this.nameInput.fill(name);
        if (intervalDays) {
            await this.intervalInput.fill(intervalDays);
        }
        await this.submitButton.click();
    }
}
