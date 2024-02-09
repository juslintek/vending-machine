<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\VendingMachine;

class VendingMachineRepository implements VendingMachineRepositoryInterface
{
    private VendingMachine $vendingMachine;

    public function __construct(VendingMachine $vendingMachine)
    {
        $this->vendingMachine = $vendingMachine;
    }

    public function purchaseItem(string $itemName, array $payment): array
    {
        return $this->vendingMachine->purchaseItem($itemName, $payment);
    }

    public function getAvailableItems(): array
    {
        return $this->vendingMachine->getInventory();
    }

    public function getAvailableChange(): array
    {
        return $this->vendingMachine->getCacheReserve();
    }
}
