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
     * @param  $data
     * @param int $opcode
     * @return bool
     */
    public function doSend(string $server_id, int $server_fd, $data, int $opcode = 0): bool;

    /**
     * Notes: 批量发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:20
     * @param int $uid
     * @param  $data
     * @param int $opcode
     * @return bool
     */
    public function send(int $uid, $data, int $opcode = 0): bool;

    /**
     * Notes: 批量发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:21
     * @param array $uids
     * @param  $data
     * @param int $opcode
     * @return int
     */
    public function sendMulti(array $uids, $data, int $opcode = 0): int;

    /**
     * Notes: 给所有有效连接发送消息
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:21
     * @param  $data
     * @param int $opcode
     * @return bool
     */
    public function sendAll($data, int $opcode = 0): bool;

    /**
     * Notes: 发送消息给本地FD
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:24
     * @param int $fd
     * @param  $data
     * @return void
     */
    public function sendToLocal(int $fd, $data, int $opcode = 0): void;

    /**
     * Notes: 获取本地服务器所有连接
     * User: 陈朋
     * DateTime: 2022/6/30 下午6:13
     * @return Iterator
     */
    public function localFds(): Iterator;
}