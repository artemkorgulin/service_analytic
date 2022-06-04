beforeEach(() => {
    cy.visit('/');
    cy.window().then(async window => {
        await window.$nuxt.$auth.loginWith('local', {
            data: {
                email: 'toyij39180@tripaco.com',
                password: 'testfrom1904',
            },
        });
    });
});

describe('Visit pages', () => {
    it('products pages', () => {
        cy.visit('/ozon/products');
        cy.get('.menu__btn').click();
        cy.get('.product-table .product-table__block').should('have.length', 10);

        cy.get('.product-table__description.txt-link').first().click();
    });

    it('adm pages', () => {
        cy.visit('/ozon/adm');
        cy.get('.menu__btn').click();
    });
});
