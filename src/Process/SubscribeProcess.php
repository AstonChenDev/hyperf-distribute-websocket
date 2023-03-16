<?php

declare(strict_types=1);

namespace Aston\DistributeWs\Process;

use Aston\DistributeWs\Contract\ISender;
use Aston\DistributeWs\Contract\ISocketClientService;
use Aston\DistributeWs\DistributeMsg;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Redis\RedisFactory;

/**
 * 订阅redis频道进程
 */
class SubscribeProcess extends AbstractProcess
{
    public string $name = 'subscribe_process';

    public function handle(): void
    {
        $server_id = $this->container->get(ISocketClientService::class)->getServerId();
        $pool = $this->container->get(ConfigInterface::class)->get('redis.pool') ?? 'default';
        $redis = $this->container->get(RedisFactory::class)->get($pool);
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);
        $redis->subscribe([
            $server_id,
            ISender::SERVER_CHANNEL
        ], [
            $this,
            'dispatchChannel'
        ]);
    }

    /**
     * Notes: 调度订阅事件回调
     * User: 陈朋
     * DateTime: 2022/6/30 下午6:09
     * @param $redis
     * @param string $channel
     * @param string $msg
     * @return void
     */
    public function dispatchChannel($redis, string $channel, string $msg): void
    {
        /** @var DistributeMsg $distribute_msg */
        $distribute_msg = unserialize($msg);
        $data = $distribute_msg->getMsg();
        $fd = $distribute_msg->getFd();
        $opcode = $distribute_msg->getOpcode();

        if ($channel === ISender::SERVER_CHANNEL) {
            foreach ($this->container->get(ISender::class)->localFds() as $local_fd) {
                $this->container->get(ISender::class)->sendToLocal($local_fd, $data, $opcode);
            }
            return;
        }
        $this->container->get(ISender::class)->sendToLocal($fd, $data, $opcode);
    }
}
