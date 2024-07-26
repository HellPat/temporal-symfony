<?php

/**
 * This file is part of Temporal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Subscription;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\ScheduleClient;
use Temporal\Client\ScheduleClientInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Exception\Client\WorkflowNotFoundException;

class CancelCommand extends Command
{
    protected WorkflowClientInterface $workflowClient;
    protected ScheduleClientInterface $scheduleClient;

    public function __construct(ServiceClient $grpc)
    {
        parent::__construct('subscription:cancel');
        $this->workflowClient = WorkflowClient::create($grpc);
        $this->scheduleClient = ScheduleClient::create($grpc);
    }

    public function configure(): void
    {
        $this->addArgument('user_id', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $userID = $input->getArgument('user_id');

        $workflow = $this->workflowClient->newUntypedRunningWorkflowStub('subscription:' . $userID);

        try {
            $workflow->cancel();
            $output->writeln('Cancelled');
        } catch (WorkflowNotFoundException $e) {
            $output->writeln('<fg=red>Already stopped</fg=red>');
        }

        return self::SUCCESS;
    }
}