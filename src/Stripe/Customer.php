<?php

namespace App\Stripe;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Index(fields: ['email'], name: 'email')]
readonly class Customer
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 255, unique: true)]
        public string $id,
        #[ORM\Column(type: 'string', length: 320)]
        public string $email,
        #[ORM\Column(type: 'string', length: 255)]
        public string $name,
        #[ORM\Column(type: 'text')]
        public string $description,
        #[ORM\Column(type: 'datetime_immutable')]
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
