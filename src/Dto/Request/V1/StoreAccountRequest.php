<?php

namespace App\Dto\Request\V1;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class StoreAccountRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'balance is required')]
        #[Assert\Type(type: 'numeric', message: 'balance must be a numeric value')]
        #[Assert\Positive(message: 'balance must be greater than zero')]
        #[Assert\Regex(
            pattern: '/^\d+(\.\d{1,2})?$/',
            message: 'balance can have at most 2 decimal places'
        )]
        public string $balance,

        #[Assert\NotBlank(message: 'currency is required')]
        #[Assert\Length(exactly: 3)]
        #[Assert\Regex(
            pattern: '/^[A-Z]{3}$/', 
            message: 'currency must be a 3-letter ISO code'
        )]
        public string $currency,

    ) {
    }
}