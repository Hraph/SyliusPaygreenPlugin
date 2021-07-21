<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Command;

use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaygreenSynchronizeShopsCommand extends Command
{
    protected static $defaultName = "paygreen:synchronize:shops";

    /**
     * @var PaygreenApiManager
     */
    private PaygreenApiManager $apiManager;

    /**
     * SynchronizePaygreenShopsCommand constructor.
     * @param PaygreenApiManager $apiManager
     */
    public function __construct(PaygreenApiManager $apiManager)
    {
        parent::__construct(null);
        $this->apiManager = $apiManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronize the Shop database with PayGreen API. API is the single source of truth and all local data will be replaced.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->apiManager->synchronizeShops();

        if ($result->isSuccess()){
            $output->writeln("{$result->getSuccessCount()} shops synchronized");
            return Command::SUCCESS;
        }
        else {
            $output->writeln("Error in shop synchronization:");
            $output->writeln($result->getMessage());
            return Command::FAILURE;
        }
    }


}
