/// <reference types="cypress" />
import Stripe from 'stripe';
import { ulid } from 'ulid';

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
    const customer = (await stripe.customers.create({
      email: email,
    }))
    
    const id = customer.id;

    // TODO: retry the reload until it's there
    //       waiting is bad, as it slows down the tests
    cy.wait(500)
    cy.reload()

    // check if the email address is visible in the list of customers
    cy.get('[data-test="customer.list"] tbody > tr', { timeout: 10000 }).contains(email)
  })
})
