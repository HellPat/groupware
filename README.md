# Groupware

A reference implementation that combines some best practices for building a modern web application.

## Features

- [x] nix-shell bases development environment
- [x] e2e-Tests with Cypress
- [x] asynchronous processing of incoming webhooks

## Installation

### Prerequisites

1. Install [Nix](https://nixos.org/nix/download.html)

### Setup

1. Clone this repository
2. Run `nix-shell` in the root directory of this repository
3. Run `just build`
4. Run `just start`
5. Open http://localhost:8000