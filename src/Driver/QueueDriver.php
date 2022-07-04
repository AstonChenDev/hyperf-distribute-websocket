<?php

namespace Aston\DistributeWs\Driver;

use Aston\DistributeWs\DistributeMsg;
use Aston\DistributeWs\Job\PushJob;

class QueueDriver extends Driver
{
    protected function sendDistribute(string $server_id, int $server_fd, $data, int $opcode = 0): bool
    {
        return $this
            ->container
            ->get(MultiRedisDriverFactory::class)
            ->get($server_id)
            ->push(new PushJob(make(DistributeMsg::class, [$data, $server_fd, $opcode])));
    }

    /**
     * Notes: 给所有有效连接发送消息
     * User: 陈朋
     * DateTime: 2022/7/4 下午3:43
     * @param $data
     * @param int $opcode
     * @return bool
     */
    public function sendAll($data, int $opcode = 0): bool
    {
        // TODO: Implement sendAll() method.
        return true;
    }
}