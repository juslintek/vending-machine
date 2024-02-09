<?php

namespace App\Repository;

interface VendingMachineRepositoryInterface
{
    public function purchaseItem(string $itemName, array $payment): array;
    public function getAvailableItems(): array;
    public function getAvailableChange(): array;
}

