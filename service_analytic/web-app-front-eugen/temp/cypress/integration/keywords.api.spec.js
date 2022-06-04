const login = 'toyij39180@tripaco.com';
const password = 'testfrom1904';
beforeEach(() => {
    cy.request({
        method: 'POST',
        url: '/api/v1/sign-in',
        body: {
            email: login,
            password,
        },
    }).then(res => {
        if (res.status !== 200 || !res?.body.token) {
            console.error('Authorization failed');
        }
        const headers = {
            Authorization: `Bearer ${res.body.token}`,
        };
        cy.wrap(headers).as('defaultHeaders');
    });
});
const id = '400232';
describe('keywords api', () => {
    it('should fetch keywords', function () {
        cy.request({
            method: 'GET',
            url: `/api/adm/v2/campaign/${id}/keywords`,
            headers: this.defaultHeaders,
        }).then(response => {
            expect(response.status).to.eq(200);
            expect(JSON.parse(response.body)).to.have.property('success', true);
            expect(JSON.parse(response.body)).to.have.property('data').to.be.an('array');
        });
    });
});
