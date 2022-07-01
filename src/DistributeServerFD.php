<?php

namespace Aston\DistributeWs;

use Aston\DistributeWs\Contract\ISender;
use Hyperf\Utils\ApplicationContext;

class DistributeServerFD implements \Stringable
{

    private string $server_id;

    private int $server_fd;

    private string $distribute_fd;

    public function __construct(string $distribute_fd, string $server_id, int $server_fd)
    {
        $this->distribute_fd = $distribute_fd;
        $this->server_id = $server_id;
        $this->server_fd = $server_fd;
    }

    /**
     * Notes: 发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午4:47
     * @param  $data
     * @param int $opcode
     * @return bool
     */
    public function send($data, int $opcode = WEBSOCKET_OPCODE_BINARY): bool
    {
        return ApplicationContext::getContainer()->get(ISender::class)->doSend($this->server_id, $this->server_fd, $data, $opcode);
    }

    /**
     * @return string
     */
    public function getServerId(): string
    {
        return $this->server_id;
    }

    /**
     * @return int
     */
    public function getServerFd(): int
    {
        return $this->server_fd;
    }

    public function __toString(): string
    {
        return $this->distribute_fd;
    }

    public function toString(): string
    {
        return $this->__toString();
    }
}