<?php

/**
 * This file is part of Temporal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Subscription;

use Carbon\CarbonInterval;
use Temporal\Activity\ActivityOptions;
use Temporal\Exception\Failure\CanceledFailure;
use Temporal\Workflow;
use Temporal\Workflow\SignalMethod;

/**
 * Demonstrates long-running process to represent user subscription process.
 */
class SubscriptionWorkflow implements SubscriptionWorkflowInterface
{
    private $account;
    private bool $suspended = false;

    public function __construct()
    {
        $this->account = Workflow::newActivityStub(
            AccountActivityInterface::class,
            ActivityOptions::new()
                ->withScheduleToCloseTimeout(CarbonInterval::seconds(60))
        );
    }

    public function subscribe(string $userID)
    {
        yield $this->account->sendWelcomeEmail($userID);

        try {
            $trialPeriod = true;
            while (true) {
                // Lower period duration to observe workflow behaviour
                yield Workflow::timer(CarbonInterval::seconds(10));

                if ($trialPeriod) {
                    yield $this->account->sendEndOfTrialEmail($userID);
                    $trialPeriod = false;
                    continue;
                }

                yield Workflow::await(fn()=> !$this->suspended);
                yield $this->account->chargeMonthlyFee($userID);
                yield $this->account->sendMonthlyChargeEmail($userID);
            }
        } catch (CanceledFailure $e) {
            yield Workflow::asyncDetached(
                function () use ($userID) {
                    yield $this->account->processSubscriptionCancellation($userID);
                    yield $this->account->sendSorryToSeeYouGoEmail($userID);
                }
            );
        }
    }

    public function suspend(): void
    {
        $this->suspended = true;
    }

    public function resume(): void
    {
        $this->suspended = false;
    }
}
