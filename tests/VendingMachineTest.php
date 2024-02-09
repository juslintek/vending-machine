<?php

namespace App\Tests;

use App\Model\VendingMachine;
use PHPUnit\Framework\TestCase;

class VendingMachineTest extends TestCase
{
    private $vendingMachine;

    protected function setUp(): void
    {
        $inventory = ['Water' => 100, 'Soda' => 150]; // key as item name and value as price
        $cashReserve = [1 => 100, 5 => 50, 10 => 50]; // key as denomination and value as count
        $this->vendingMachine = new VendingMachine($inventory, $cashReserve);
    }

    public function testPurchaseExactChange(): void
    {
        $change = $this->vendingMachine->purchaseItem('Water', [100]);
        $this->assertEmpty($change, 'Expected no change when paying with exact change.');
    }

    public function testPurchaseWithOverpayment(): void
    {
        $change = $this->vendingMachine->purchaseItem('Soda', [100, 100]); // Overpaying by 50 cents
        $expectedChange = [10 => 5]; // Expecting 5 x 10ct coins as change
        $this->assertEquals($expectedChange, $change, 'Expected correct change for overpayment.');
    }

    public function testInsufficientPayment(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Insufficient payment");
        $this->vendingMachine->purchaseItem('Soda', [100]); // Insufficient payment
    }

    public function testCannotProvideChange(): void
    {
        // Emptying the cash reserve to simulate unable to provide change scenario
        $this->vendingMachine = new VendingMachine(['Soda' => 150], [100 => 1]); // Only one 100ct coin in reserve

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot provide change");
        $this->vendingMachine->purchaseItem('Soda', [100, 100]); // Cannot provide change for overpayment
    }
}
