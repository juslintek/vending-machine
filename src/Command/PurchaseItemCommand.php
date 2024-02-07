<?php

namespace App\Command;

use App\Model\VendingMachine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class PurchaseItemCommand extends Command
{
    protected static $defaultName = 'app:purchase-item';
    private VendingMachine $vendingMachine;

    public function __construct()
    {
        parent::__construct();
        /** @var array<string, int> $inventory key as item name and value as price in cents */
        $inventory = ['Water' => 90, 'Soda' => 150, 'Coca-Cola' => 183];
        /** @var array<int, int> $cashReserve key as nominal value in cents and value as number of coins */
        $cashReserve = [1 => 100, 5 => 50, 10 => 50];
        $this->vendingMachine = new VendingMachine($inventory, $cashReserve);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Purchase an item from the vending machine.')
            ->setHelp('This command allows you to select and purchase an item from the vending machine');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the item you wish to purchase: ');
        $itemName = $helper->ask($input, $output, $question);

        $question = new Question('Enter the coins you are paying with (comma-separated, e.g., 25,25,10): ');
        $coinsInput = $helper->ask($input, $output, $question);
        $coins = array_map('intval', explode(',', $coinsInput));

        try {
            $change = $this->vendingMachine->purchaseItem($itemName, $coins);
            $output->writeln("Item purchased successfully. Your change: " . json_encode($change));
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
