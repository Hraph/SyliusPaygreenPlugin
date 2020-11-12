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

    protected function configure()
    {
        $this->setName(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->apiManager->synchronizeShops();

        if ($result->isSuccess()){
            $output->writeln("{$result->getSuccessCount()} shops synchronized");
        }
        else {
            $output->writeln("Error in shop synchronization:");
            $output->writeln($result->getMessage());
        }
    }


}
