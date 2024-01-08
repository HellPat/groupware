<?php

namespace App\Website;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/pricing', methods: ['GET'])]
final class ComparePlansAction
{
    public function __invoke()
    {
        return new Response('
            <html>
                <body>
                    <h1>Compare Plans</h1>
                    
                    <script async src="https://js.stripe.com/v3/pricing-table.js"></script>
                    <stripe-pricing-table pricing-table-id="prctbl_1OWNEUH5sb6o9949OYzQOqjC"
                    publishable-key="pk_test_51IHFU3H5sb6o9949ao7U2zG18HsUdNIPAcwrVdmHKfMS0FUsoUYwmSh2SNvM0fy8JpRxg6nraWmVRZR12M74Z9q500c4Kb7si2">
                    </stripe-pricing-table>
                </body>
            </html>
        ');
    }
}
