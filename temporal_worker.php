<?php

declare(strict_types=1);

use Temporal\WorkerFactory;

ini_set('display_errors', 'stderr');
include "vendor/autoload.php";

// factory initiates and runs task queue specific activity and workflow workers
$factory = WorkerFactory::create();

// Worker that listens on a task queue and hosts both workflow and activity implementations.
$worker = $factory->newWorker();

// Workflows are stateful. So you need a type to create instances.
$worker->registerWorkflowTypes(\App\Subscription\SubscriptionWorkflow::class);

$worker->registerActivity(\App\Subscription\AccountActivity::class);

// start primary loop
$factory->run();