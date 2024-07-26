<?php

/**
 * This file is part of Temporal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Subscription;

use Temporal\Workflow\SignalMethod;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
interface SubscriptionWorkflowInterface
{
    #[WorkflowMethod]
    public function subscribe(
        string $userID
    );

    #[SignalMethod]
    public function suspend(): void;

    #[SignalMethod]
    public function resume(): void;
}