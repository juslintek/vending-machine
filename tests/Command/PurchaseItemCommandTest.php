<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\PurchaseItemCommand;
use App\Model\VendingMachine;
use App\Repository\VendingMachineRepository;
use App\Repository\VendingMachineRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class PurchaseItemCommandTest extends TestCase
{
    private CommandTester $commandTester;

    /** @var MockObject|VendingMachineRepository|VendingMachineRepository&MockObject */
    private MockObject|VendingMachineRepository $repositoryMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(VendingMachineRepositoryInterface::class);
        $application = new Application();
        $application->add(new PurchaseItemCommand($this->repositoryMock));
        $application->setAutoExit(false);
        $command = $application->find('app:purchase-item');
        $this->commandTester = new CommandTester($command);
    }

    public function testListAvailableItems(): void
    {
        // Setup repository mock to return a list of items
        $this->repositoryMock->method('getAvailableItems')->willReturn([
            'Water' => 100,
            'Soda' => 150,
        ]);
        $this->repositoryMock->method('getAvailableChange')->willReturn([200 => 3]);

        $this->commandTester->setInputs(['Soda', '3 x 2 Eur']);
        // Execute the command without user input to test listing functionality
        $this->commandTester->execute([], ['interactive' => false, 'decorated' => true]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Water 1eur, 0ct', $output);
        $this->assertStringContainsString('Soda 1eur, 50ct', $output);
    }

    public function testPurchaseItem(): void
    {
        $this->repositoryMock->method('getAvailableItems')->willReturn(['Water' => 100]);
        $this->repositoryMock->method('getAvailableChange')->willReturn([100 => 1]);
        // Setup repository mock for purchasing an item
        $this->repositoryMock->expects($this->once())
            ->method('purchaseItem')
            ->with('Water', [100])
            ->willReturn([]);

        // Simulate user input for selecting 'Water' and paying with a single 100ct coin
        $this->commandTester->setInputs(['0', '1 x 100ct']);
        $this->commandTester->execute(['--test' => true], ['interactive' => true, 'decorated' => true]);

        $this->assertStringContainsString('Purchase successful!', $this->commandTester->getDisplay());
    }

    public function testInsufficientPayment(): void
    {
        $this->repositoryMock->method('getAvailableItems')->willReturn(['Water' => 100]);
        $this->repositoryMock->method('getAvailableChange')->willReturn([100 => 1, 5 => 100]);

        // Setup repository mock to throw an exception for insufficient payment
        $this->repositoryMock->method('purchaseItem')->willThrowException(new \Exception("Insufficient payment"));

        // Simulate user input for selecting 'Soda' and underpaying
        $this->commandTester->setInputs(['0', '1 x 50ct']);
        $this->commandTester->execute(['--test' => true], ['interactive' => true, 'decorated' => true]);

        $this->assertStringContainsString('Error: Insufficient payment', $this->commandTester->getDisplay());
    }

    public function testItemNotAvailable(): void
    {
        // Assume 'Juice' is not available in the inventory
        $this->repositoryMock->method('purchaseItem')
            ->with('Juice', [100])
            ->willThrowException(new \Exception("Item not available"));

        // Simulate user input for selecting 'Juice' and paying with a 100ct coin
        $this->commandTester->setInputs(['Juice', '1 x 100ct']);
        $this->commandTester->execute(['--test' => true], ['interactive' => true, 'decorated' => true]);

        // Check that the correct error message is displayed
        $this->assertStringContainsString('Product Juice is invalid.', $this->commandTester->getDisplay());
    }
}