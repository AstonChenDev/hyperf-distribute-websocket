<?php

namespace Aston\DistributeWs\Contract;

use Iterator;

interface ISender
{
    const SERVER_CHANNEL = 'server_channel';

    /**
     * Notes: 执行发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:19
     * @param string $server_id
     * @param int $server_fd
     * @param string $data
     * @return bool
     */
    public function doSend(string $server_id, int $server_fd, string $data): bool;

    /**
     * Notes: 批量发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:20
     * @param int $uid
     * @param string $data
     * @return bool
     */
    public function send(int $uid, string $data): bool;

    /**
     * Notes: 批量发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:21
     * @param array $uids
     * @param string $data
     * @return int
     */
    public function sendMulti(array $uids, string $data): int;

    /**
     * Notes: 给所有有效连接发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:21
     * @param string $data
     * @return bool
     */
    public function sendAll(string $data): bool;

    /**
     * Notes: 发送消息给本地FD
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:24
     * @param int $fd
     * @param string $data
     * @return void
     */
    public function sendToLocal(int $fd, string $data): void;

    /**
     * Notes: 获取本地服务器所有连接
     * User: 陈朋
     * DateTime: 2022/6/30 下午6:13
     * @return Iterator
     */
    public function localFds(): Iterator;
}