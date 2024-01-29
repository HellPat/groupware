/// <reference types="cypress" />
import Stripe from 'stripe';
import {ulid} from 'ulid';
import 'cypress-recurse/commands'

// TODO: move to environment variable
// set your api key with an environment variable `CYPRESS_API_KEY` or configure using `env` property in config file
// (cypress prefixes environment variables with CYPRESS)
// const apiKey = Cypress.env('API_KEY')
const stripe = new Stripe('sk_test_51IHFU3H5sb6o9949VJorc2HKKiXJ8Q5gh4cSKrRrvHmu2FRVoIYoHxygkUEji2WgKFgVpmYxHnV7WDjRLm3sUyIx00HIEjLSeh');

describe('customers', () => {
    beforeEach(() => {
        // Cypress starts out with a blank state for each test,
        // so we must tell it to visit our website with the `cy.visit()` command.
        // Since we want to visit the same URL at the start of all our tests,
        // we include it in our beforeEach function so that it runs before each test

        // TODO: move to environment variable
        cy.visit('http://localhost:8000/customers')
    })

    it('it adds new stripe users to the list', async () => {
        const email = `customer-${ulid()}@e2e.com`;
        const customer = await stripe.customers.create({
            email: email,
        })

        cy.recurse(
            () => cy.get(`[data-test-customer-email="${email}"]`).should(Cypress._.noop),
            (el) => {
                return el && el.text() === email
            },
            {
                limit: 10,
                delay: 2000, // sleep for 2 second before reloading the page
                timeout: 60000, // retry for 60 seconds
                post: () => {
                    cy.reload()
                },
            },
        )

        cy.wrap(null).then(() => stripe.customers.del(customer.id))

        cy.recurse(
            () => cy.get(`[data-test-customer="${customer.id}"]`).should(Cypress._.noop),
            (el) => el.length === 0,
            {
                limit: 10,
                delay: 2000, // sleep for 2 second before reloading the page
                timeout: 60000, // retry for 60 seconds
                post: () => {
                    cy.reload()
                },
            },
        )
    })
})
