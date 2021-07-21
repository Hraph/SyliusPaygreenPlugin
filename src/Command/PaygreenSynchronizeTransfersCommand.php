<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Command;

use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaygreenSynchronizeTransfersCommand extends Command
{
    protected static $defaultName = "paygreen:synchronize:transfers";

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

    protected function configure()
    {
        $this->setName(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->apiManager->synchronizeTransfers();

        if ($result->isSuccess()){
            $output->writeln("{$result->getSuccessCount()} transfers synchronized");
            return Command::SUCCESS;
        }
        else {
            $output->writeln("Error in transfers synchronization:");
            $output->writeln($result->getMessage());
            return Command::FAILURE;
        }
    }


}
