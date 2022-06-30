<?php

namespace Aston\DistributeWs\Contract;

use Aston\DistributeWs\DistributeServerFD;

interface ISocketClientService
{

    /**
     * Notes: 获取serverID
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:28
     * @return string
     */
    public function getServerId(): string;

    /**
     * Notes: 客户端fd与用户ID绑定关系 返回用户的分布式FD
     * User: 陈朋
     * DateTime: 2021/11/9 16:57
     * @param int $fd
     * @param int $user_id
     * @return DistributeServerFD
     */
    public function bindRelation(int $fd, int $user_id): DistributeServerFD;

    /**
     * Notes: 解除指定的客户端fd与用户绑定关系
     * User: 陈朋
     * DateTime: 2021/11/9 16:57
     * @param string $fd
     */
    public function removeRelation(string $fd): void;

    /**
     * Notes: 查询客户端fd对应的用户ID
     * User: 陈朋
     * DateTime: 2021/11/9 16:57
     * @param string $fd
     * @return int
     */
    public function findFdUserId(string $fd): int;

    /**
     * Notes: 获取用户的FD
     * User: 陈朋
     * DateTime: 2021/11/9 17:05
     * @param int $user_id
     * @return DistributeServerFD | null
     */
    public function findUserFd(int $user_id): ?DistributeServerFD;

    /**
     * Notes: 解析分布式fd为server_id 和 该server 的fd
     * User: 陈朋
     * DateTime: 2022/6/30 下午4:36
     * @param string $fd
     * @return array
     */
    public function parseDistributeFD(string $fd): array;
}