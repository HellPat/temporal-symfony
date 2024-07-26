<?php

/**
 * This file is part of Temporal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Subscription;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\ScheduleClient;
use Temporal\Client\ScheduleClientInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\Common\IdReusePolicy;
use Temporal\Exception\Client\WorkflowExecutionAlreadyStartedException;
use Temporal\SampleUtils\Command;

class SubscribeCommand extends \Symfony\Component\Console\Command\Command
{
    protected WorkflowClientInterface $workflowClient;
    protected ScheduleClientInterface $scheduleClient;

    public function __construct(ServiceClient $grpc)
    {
        parent::__construct('subscription:start');
        $this->workflowClient = WorkflowClient::create($grpc);
        $this->scheduleClient = ScheduleClient::create($grpc);
    }

    protected function configure(): void
    {
        $this->addArgument('user_id', InputArgument::REQUIRED);
        $this->addArgument('user_name', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $userID = $input->getArgument('user_id');

        $workflow = $this->workflowClient->newWorkflowStub(
            SubscriptionWorkflowInterface::class,
            WorkflowOptions::new()
                ->withWorkflowId('subscription:' . $userID)
                ->withWorkflowIdReusePolicy(IdReusePolicy::POLICY_ALLOW_DUPLICATE)
        );

        $output->writeln("Start <comment>SubscriptionWorkflow</comment>... ");

        try {
            $run = $this->workflowClient->start($workflow, $userID);
        } catch (WorkflowExecutionAlreadyStartedException $e) {
            $output->writeln('<fg=red>Already running</fg=red>');
            return self::SUCCESS;
        }

        $output->writeln(
            sprintf(
                'Started: WorkflowID=<fg=magenta>%s</fg=magenta>, RunID=<fg=magenta>%s</fg=magenta>',
                $run->getExecution()->getID(),
                $run->getExecution()->getRunID(),
            )
        );

        return self::SUCCESS;
    }
}