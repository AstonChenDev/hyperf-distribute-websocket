<?php

namespace Aston\DistributeWs\Implement;

use Aston\DistributeWs\Contract\ISender;
use Aston\DistributeWs\Contract\ISocketClientService;
use Aston\DistributeWs\DistributeMsg;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisFactory;
use Hyperf\Server\ServerFactory;
use Hyperf\Utils\ApplicationContext;
use Iterator;

class Sender implements ISender
{
    /**
     * Notes: 执行发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:19
     * @param string $server_id
     * @param int $server_fd
     * @param  $data
     * @param int $opcode
     * @return bool
     */
    public function doSend(string $server_id, int $server_fd, $data, int $opcode = WEBSOCKET_OPCODE_BINARY): bool
    {
        $container = ApplicationContext::getContainer();

        if ($container->get(ISocketClientService::class)->getServerId() === $server_id) {
            $this->sendToLocal($server_fd, $data, $opcode);
            return true;
        }

        $pool = $container->get(ConfigInterface::class)->get('redis.pool') ?? 'default';
        return (bool)$container
            ->get(RedisFactory::class)
            ->get($pool)
            ->publish(
                $server_id,
                serialize(make(DistributeMsg::class, [$data, $server_fd, $opcode]))
            );
    }

    /**
     * Notes: 批量发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:20
     * @param int $uid
     * @param  $data
     * @param int $opcode
     * @return bool
     */
    public function send(int $uid, $data, int $opcode = WEBSOCKET_OPCODE_BINARY): bool
    {
        $fd = ApplicationContext::getContainer()->get(ISocketClientService::class)->findUserFd($uid);
        if (!$fd) {
            return false;
        }
        return $fd->send($data, $opcode);
    }

    /**
     * Notes: 批量发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:21
     * @param array $uids
     * @param  $data
     * @param int $opcode
     * @return int
     */
    public function sendMulti(array $uids, $data, int $opcode = WEBSOCKET_OPCODE_BINARY): int
    {
        $count = 0;
        foreach ($uids as $uid) {
            if ($this->send($uid, $data)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Notes: 给所有有效连接发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:21
     * @param  $data
     * @param int $opcode
     * @return bool
     */
    public function sendAll($data, int $opcode = WEBSOCKET_OPCODE_BINARY): bool
    {
        return $this->doSend(self::SERVER_CHANNEL, 0, $data, $opcode);
    }

    /**
     * Notes: 发送消息给本地FD
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:25
     * @param int $fd
     * @param  $data
     * @param int $opcode
     * @return void
     */
    public function sendToLocal(int $fd, $data, int $opcode = WEBSOCKET_OPCODE_BINARY): void
    {
        $server = ApplicationContext::getContainer()->get(ServerFactory::class)->getServer()->getServer();
        $client_info = $server->getClientInfo($fd);
        if (isset($client_info['websocket_status']) && $client_info['websocket_status'] === WEBSOCKET_STATUS_ACTIVE) {
            $server->push($fd, $data, $opcode);
        }
    }

    /**
     * Notes: 获取本地服务器所有连接
     * User: 陈朋
     * DateTime: 2022/6/30 下午6:14
     * @return Iterator
     */
    public function localFds(): Iterator
    {
        return ApplicationContext::getContainer()->get(ServerFactory::class)->getServer()->getServer()->connections;
    }
}