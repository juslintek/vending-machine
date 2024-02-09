<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\VendingMachineRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:purchase-item',
    description: 'Purchase an item from the vending machine.',
)]
class PurchaseItemCommand extends Command
{
    private VendingMachineRepositoryInterface $vendingMachineRepository;

    public function __construct(VendingMachineRepositoryInterface $vendingMachineRepository, ?string $name = null)
    {
        parent::__construct();
        $this->vendingMachineRepository = $vendingMachineRepository;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to select and purchase an item from the vending machine')
            ->addOption(
                'test',
                null,
                InputOption::VALUE_NONE,
                'Run the command in test mode (exit after one iteration)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        do {
            $items = $this->vendingMachineRepository->getAvailableItems();
            $itemNames = [];
            foreach ($items as $name => $price) {
                $itemNames[$name] = ' -- ' . $name . ' ' . ($price >= 100 ? (round((int)$price / 100, 0, PHP_ROUND_HALF_DOWN) . 'eur, ' . ((int)$price % 100) . 'ct') : $price . 'ct');
            }

            $output->write([
                'This is vending machine coin change simulator. You can purchase items using coins.',
                'Available items:',
                ...$itemNames,
                'Available change:',
                $this->formatChangeOutput($this->vendingMachineRepository->getAvailableChange()),
            ], true);

            $itemNames['Quit'] = 'Quit';

            $helper = $this->getHelper('question');
            $isTestMode = $input->getOption('test');
            $question = new ChoiceQuestion(
                'Please select a product:',
                array_keys($itemNames),
                count($itemNames) - 1 // Default to 'Quit'
            );

            $question->setErrorMessage('Product %s is invalid.');

            $selectedItem = $helper->ask($input, $output, $question);

            if ($selectedItem === 'Quit' || strtoupper($selectedItem) === 'Q') {
                $output->writeln('Exiting the vending machine. Goodbye!');
                break;
            }

            $output->writeln("You selected: $selectedItem");

            $question = new Question('Enter the amount and denomination of coins you are paying with (e.g., "1 x 1eur, 5 x 10ct"): ');
            $coinsString = $helper->ask($input, $output, $question);
            $output->writeln("You entered: $coinsString");
            $payment = $this->parseCoinsInput($coinsString);


            try {
                $change = $this->vendingMachineRepository->purchaseItem($selectedItem, $payment);
                $formattedChange = $this->formatChangeOutput($change);
                $output->writeln("<fg=green;options=bold>Purchase successful! Your change: $formattedChange</>");
            } catch (\Exception $e) {
                $output->writeln("<fg=red;options=bold>Error: " . $e->getMessage() . '</>');
                return Command::FAILURE;
            }

            if ($isTestMode) {
                break; // Exit the loop if in test mode
            }

            // Prompt to continue or quit after each transaction
            $output->writeln("Press 'Q' to quit or any other key to continue...");
            $continue = stream_get_line(STDIN, 1024, PHP_EOL); // Read a line from STDIN
        } while (strtoupper(trim($continue)) !== 'Q');

        return Command::SUCCESS;
    }

    // Parse input like "2 x 20ct" and return an array of coin denominations like [20, 20]
    private function parseCoinsInput(string $input): array
    {
        $coins = [];
        $patterns = explode(',', $input); // Split by comma for multiple coin inputs

        foreach ($patterns as $pattern) {
            if (preg_match('/(\d+)\s*x\s*(\d+)(ct|eur)/', trim($pattern), $matches)) {
                $quantity = (int)$matches[1];
                $coinValue = (int)$matches[2];
                $currency = $matches[3];
                if ($currency === 'eur') {
                    $coinValue *= 100;
                }

                for ($i = 0; $i < $quantity; $i++) {
                    $coins[] = $coinValue;
                }
            }
        }

        return $coins;
    }

    // Format change array into a string like "1 x 10ct and 1 x 5ct"
    private function formatChangeOutput(array $change): string
    {
        $formattedParts = [];
        foreach ($change as $coin => $quantity) {
            if ($coin >= 100) {
                $coin = (int)((int)$coin / 100);
                $formattedParts[] = "$quantity x {$coin}eur";
            } else {
                $formattedParts[] = "$quantity x {$coin}ct";
            }
        }
        return implode(' and ', $formattedParts);
    }
}
