<?php

declare(strict_types=1);

namespace App\Model;

use App\Generator\Generator;

class VendingMachine
{
    private array $inventory;
    private array $cashReserve;

    public function __construct(array $inventory, array|Generator $cashReserve)
    {
        $this->inventory = $inventory;
        $this->cashReserve = $cashReserve instanceof Generator ? $cashReserve->generate() : $cashReserve;
    }

    public function purchaseItem(string $itemName, array $payment): array
    {
        if (!isset($this->inventory[$itemName])) {
            throw new \RuntimeException("Item not available");
        }
        $itemPrice = $this->inventory[$itemName];
        $totalPayment = array_sum($payment);

        if ($totalPayment < $itemPrice) {
            throw new \RuntimeException("Insufficient payment");
        }

        $changeAmount = $totalPayment - $itemPrice;

        $change = $this->calculateChange($changeAmount);
        if ($change === null) {
            throw new \RuntimeException("Cannot provide change");
        }

        $this->updateCashReserve($payment, $change);
        return $change;
    }

    public function getInventory(): array
    {
        return $this->inventory;
    }

    public function getCacheReserve(): array
    {
        return $this->cashReserve;
    }

    private function calculateChange(int $amount): ?array
    {
        $change = [];

        $cacheReserve = $this->getCacheReserve();

        krsort($cacheReserve);

        foreach ($cacheReserve as $coin => $count) {
            while ($amount >= $coin && $count > 0) {
                $amount -= $coin;
                $count--;
                if (!isset($change[$coin])) {
                    $change[$coin] = 0;
                }
                $change[$coin]++;
                $cacheReserve[$coin] = $count;
            }
        }

        if ($amount > 0) {
            return null; // Cannot provide the exact change
        }

        return $change;
    }

    private function updateCashReserve(array $payment, array $change): void
    {
        foreach ($payment as $coin) {
            if (!isset($this->cashReserve[$coin])) {
                $this->cashReserve[$coin] = 0;
            }
            $this->cashReserve[$coin]++;
        }

        foreach ($change as $coin => $count) {
            $this->cashReserve[$coin] -= $count;
        }
    }
}
