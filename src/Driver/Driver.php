<?php

namespace Aston\DistributeWs\Driver;

use Aston\DistributeWs\Contract\ISender;
use Aston\DistributeWs\Contract\ISocketClientService;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Server\ServerFactory;
use Iterator;
use Psr\Container\ContainerInterface;

abstract class Driver implements ISender
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    protected int $default_opcode;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $config = $container->get(ConfigInterface::class);
        $this->default_opcode = (int)$config->get('distribute_ws.default_opcode', WEBSOCKET_OPCODE_BINARY);
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
    public function sendToLocal(int $fd, $data, int $opcode = 0): void
    {
        $server = $this->container->get(ServerFactory::class)->getServer()->getServer();
        $client_info = $server->getClientInfo($fd);
        if (isset($client_info['websocket_status']) && $client_info['websocket_status'] === WEBSOCKET_STATUS_ACTIVE) {
            if (!$opcode) {
                $opcode = $this->default_opcode;
            }
            $server->push($fd, $data, $opcode);
        }
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
    public function send(int $uid, $data, int $opcode = 0): bool
    {
        $fd = $this->container->get(ISocketClientService::class)->findUserFd($uid);
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
    public function sendMulti(array $uids, $data, int $opcode = 0): int
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
     * Notes: 获取本地服务器所有连接
     * User: 陈朋
     * DateTime: 2022/6/30 下午6:14
     * @return Iterator
     */
    public function localFds(): Iterator
    {
        return $this->container->get(ServerFactory::class)->getServer()->getServer()->connections;
    }

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
    public function doSend(string $server_id, int $server_fd, $data, int $opcode = 0): bool
    {
        if ($this->container->get(ISocketClientService::class)->getServerId() === $server_id) {
            $this->sendToLocal($server_fd, $data, $opcode);
            return true;
        }
        return $this->sendDistribute(...func_get_args());
    }

    /**
     * Notes: 不同驱动发送分布式消息实现
     * User: 陈朋
     * DateTime: 2022/7/4 下午3:54
     * @param string $server_id
     * @param int $server_fd
     * @param $data
     * @param int $opcode
     * @return bool
     */
    abstract protected function sendDistribute(string $server_id, int $server_fd, $data, int $opcode = 0): bool;
}