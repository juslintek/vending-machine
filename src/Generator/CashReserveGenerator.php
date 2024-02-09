<?php

namespace App\Generator;

use Exception;

class CashReserveGenerator implements Generator
{
    public function __construct(
        private array $denominations,
        private readonly int $maxQuantity
    )
    {
    }

    /**
     * @throws Exception
     */
    public function generate(): array
    {
        $cashReserve = [];

        shuffle($this->denominations);
        $denominations = array_slice($this->denominations, 0, random_int(1, count($this->denominations)));
        foreach ($denominations as $value) {
            $cashReserve[$value] = random_int(1, $this->maxQuantity);
        }

        return $cashReserve;
    }
}