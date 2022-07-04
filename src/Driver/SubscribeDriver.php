<?php

namespace Aston\DistributeWs\Driver;

use Aston\DistributeWs\DistributeMsg;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisFactory;

class SubscribeDriver extends Driver
{
    protected function sendDistribute(string $server_id, int $server_fd, $data, int $opcode = 0): bool
    {
        $pool = $this->container->get(ConfigInterface::class)->get('redis.pool') ?? 'default';
        return (bool)$this->container
            ->get(RedisFactory::class)
            ->get($pool)
            ->publish(
                $server_id,
                serialize(make(DistributeMsg::class, [$data, $server_fd, $opcode]))
            );
    }

    /**
     * Notes: 给所有有效连接发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:21
     * @param  $data
     * @param int $opcode
     * @return bool
     */
    public function sendAll($data, int $opcode = 0): bool
    {
        return $this->doSend(self::SERVER_CHANNEL, 0, $data, $opcode);
    }
}