describe('Login throwgh UI', () => {
    it('should log in with credentials', () => {
        cy.visit('/login');
        cy.get('#email').type('toyij39180@tripaco.com'); // Cypress.env('login')
        cy.get('#password').type('testfrom1904'); // Cypress.env('password')
        cy.get('#submit').click();
    });
});
