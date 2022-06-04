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
const id = '299611';
describe('groups', () => {
    it('should fetch groups', function () {
        cy.request({
            method: 'GET',
            url: `/api/adm/v2/campaign/${id}/groups`,
            headers: this.defaultHeaders,
        }).then(response => {
            expect(response.status).to.eq(200);
            expect(JSON.parse(response.body)).to.have.property('success', true);
            expect(JSON.parse(response.body).data).to.have.property('groupList').to.be.an('array');
        });
    });

    it('should create new group with name', function () {
        cy.request({
            method: 'POST',
            url: `/api/adm/v2/campaign/${id}/groups`,
            headers: this.defaultHeaders,
            body: {
                name: 'lalka',
            },
        }).then(response => {
            expect(response.status).to.eq(200);
            expect(response.body).to.have.property('success', true);
            expect(response.body).to.have.property('data');
            expect(response.body.data).to.have.property('group_id');
            const groupId = response.body.data.group_id;
            cy.request({
                method: 'GET',
                url: `/api/adm/v2/campaign/${id}/groups/${groupId}`,
                headers: this.defaultHeaders,
            }).then(response => {
                expect(response.status).to.eq(200);
                expect(JSON.parse(response.body)).to.have.property('success', true);
            });
        });
    });

    it('should create new group with name and goods', function () {
        cy.request({
            method: 'POST',
            url: `/api/adm/v2/campaign/${id}/groups`,
            headers: this.defaultHeaders,
            body: {
                name: 'lalka',
                goods: [2, 3],
            },
        }).then(response => {
            expect(response.status).to.eq(200);
            expect(response.body).to.have.property('success', true);
            expect(response.body).to.have.property('data');
            expect(response.body.data).to.have.property('group_id');
            const groupId = response.body.data.group_id;
            cy.request({
                method: 'GET',
                url: `/api/adm/v2/campaign/${id}/groups/${groupId}`,
                headers: this.defaultHeaders,
            });
        });
    });

    it('should change group - name', function () {
        cy.request({
            method: 'GET',
            url: `/api/adm/v2/campaign/${id}/groups`,
            headers: this.defaultHeaders,
        }).then(response => {
            const name = 'LALKA_EDIT_TEST';
            expect(response.status).to.eq(200);
            expect(JSON.parse(response.body)).to.have.property('success', true);
            expect(JSON.parse(response.body).data).to.have.property('groupList').to.be.an('array');
            const groups = JSON.parse(response.body).data.groupList;
            const testGroup = groups[0];
            console.log('🚀 ~ file: api.spec.js ~ line 110 ~ it ~ groups', testGroup, groups);
            cy.request({
                method: 'PUT',
                url: `/api/adm/v2/campaign/${id}/groups/${testGroup.id}`,
                headers: this.defaultHeaders,
                body: {
                    name,
                },
            }).then(response => {
                expect(response.status).to.eq(200);
                expect(response.body).to.have.property('success', true);
                cy.request({
                    method: 'GET',
                    url: `/api/adm/v2/campaign/${id}/groups/${testGroup.id}`,
                    headers: this.defaultHeaders,
                }).then(response => {
                    expect(response.status).to.eq(200);
                    expect(JSON.parse(response.body)).to.have.property('success', true);
                    expect(JSON.parse(response.body).data).to.have.property('name', name);
                });
            });
        });
    });
    it('should change group - goods add', function () {
        cy.request({
            method: 'GET',
            url: `/api/adm/v2/campaign/${id}/groups`,
            headers: this.defaultHeaders,
        }).then(response => {
            // const name = 'LALKA_EDIT_TEST';
            expect(response.status).to.eq(200);
            expect(JSON.parse(response.body)).to.have.property('success', true);
            expect(JSON.parse(response.body).data).to.have.property('groupList').to.be.an('array');
            const groups = JSON.parse(response.body).data.groupList;
            const testGroup = groups[0];
            console.log('🚀 ~ file: api.spec.js ~ line 110 ~ it ~ groups', testGroup, groups);
            cy.request({
                method: 'PUT',
                url: `/api/adm/v2/campaign/${id}/groups/${testGroup.id}`,
                headers: this.defaultHeaders,
                body: {
                    goods: [2],
                },
            }).then(response => {
                expect(response.status).to.eq(200);
                expect(response.body).to.have.property('success', true);
                cy.request({
                    method: 'GET',
                    url: `/api/adm/v2/campaign/${id}/groups/${testGroup.id}`,
                    headers: this.defaultHeaders,
                }).then(response => {
                    expect(response.status).to.eq(200);
                    expect(JSON.parse(response.body)).to.have.property('success', true);
                    // expect(JSON.parse(response.body).data).to.have.property('name', name);
                });
            });
        });
    });
    it('should delete group', function () {
        cy.request({
            method: 'GET',
            url: `/api/adm/v2/campaign/${id}/groups`,
            headers: this.defaultHeaders,
        }).then(response => {
            expect(response.status).to.eq(200);
            expect(JSON.parse(response.body)).to.have.property('success', true);
            expect(JSON.parse(response.body).data).to.have.property('groupList').to.be.an('array');
            const groups = JSON.parse(response.body).data.groupList;
            const testGroup = groups[0];
            console.log('🚀 ~ file: api.spec.js ~ line 110 ~ it ~ groups', testGroup, groups);
            cy.request({
                method: 'DELETE',
                url: `/api/adm/v2/campaign/${id}/groups/${testGroup.id}`,
                headers: this.defaultHeaders,
            }).then(response => {
                expect(response.status).to.eq(200);
                expect(JSON.parse(response.body)).to.have.property('success', true);
            });
            cy.request({
                method: 'GET',
                url: `/api/adm/v2/campaign/${id}/groups/${testGroup.id}`,
                headers: this.defaultHeaders,
            }).then(response => {
                expect(response.status).to.eq(404);
                // expect(JSON.parse(response.body)).to.have.property('success', true);
            });
        });
    });
});
