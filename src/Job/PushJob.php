<?php

declare(strict_types=1);

namespace App\Job;

namespace Aston\DistributeWs\Job;

use Aston\DistributeWs\Contract\ISender;
use Aston\DistributeWs\DistributeMsg;
use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\ApplicationContext;

class PushJob extends Job
{
    public DistributeMsg $distribute_msg;

    public $unique_msg_id;

    /**
     * 任务执行失败后的重试次数，即最大执行次数为 $maxAttempts+1 次
     *
     * @var int
     */
    protected int $maxAttempts = 2;

    public function __construct(DistributeMsg $distribute_msg)
    {
        // 这里最好是普通数据，不要使用携带 IO 的对象，比如 PDO 对象
        $this->distribute_msg = $distribute_msg;
        $this->unique_msg_id = uniqid('msg-', true);
    }

    public function handle()
    {
        ApplicationContext::getContainer()->get(ISender::class)->sendToLocal($this->distribute_msg->getFd(), $this->distribute_msg->getMsg(), $this->distribute_msg->getOpcode());
    }
}
