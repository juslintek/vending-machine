<?php

namespace App\Model;

class VendingMachine
{
    private $inventory;
    private $cashReserve;

    public function __construct(array $inventory, array $cashReserve)
    {
        $this->inventory = $inventory;
        $this->cashReserve = $cashReserve;
    }

    public function purchaseItem(string $itemName, array $payment)
    {
        if (!isset($this->inventory[$itemName])) {
            throw new \Exception("Item not available");
        }
        $itemPrice = $this->inventory[$itemName];
        $totalPayment = array_sum($payment);

        if ($totalPayment < $itemPrice) {
            throw new \Exception("Insufficient payment");
        }

        $changeAmount = $totalPayment - $itemPrice;
        $change = $this->calculateChange($changeAmount);
        if ($change === null) {
            throw new \Exception("Cannot provide change");
        }

        $this->updateCashReserve($payment, $change);
        $this->inventory[$itemName]--; // Assuming each item is decremented by 1 per purchase

        return $change;
    }

    private function calculateChange(int $amount): ?array
    {
        $change = [];
        foreach ($this->cashReserve as $coin => $count) {
            while ($amount >= $coin && $count > 0) {
                $amount -= $coin;
                $count--;
                if (!isset($change[$coin])) {
                    $change[$coin] = 0;
                }
                $change[$coin]++;
                $this->cashReserve[$coin] = $count;
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
