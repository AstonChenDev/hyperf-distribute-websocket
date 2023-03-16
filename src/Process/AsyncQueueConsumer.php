<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Aston\DistributeWs\Process;

use Hyperf\AsyncQueue\Process\ConsumerProcess;

class AsyncQueueConsumer extends ConsumerProcess
{
    public string $name = 'local-async-queue';
    /**
     * @var string
     */
    protected string $queue = 'local';
}
