<?php

namespace Aston\DistributeWs\Implement;


use Aston\DistributeWs\Contract\ISocketClientService;
use Aston\DistributeWs\DistributeServerFD;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Psr\Container\ContainerInterface;
use \Redis;

/**
 * Socket客户端ID映射服务
 */
class SocketClientService implements ISocketClientService
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var array
     */
    private array $configs;

    /**
     * @var string
     */
    private string $server_id;

    /**
     * @var RedisProxy
     */
    protected RedisProxy $redis;

    /**
     * @var string
     */
    private string $user_fd_key;

    /**
     * @var string
     */
    private string $fd_user_key;

    /**
     * @var int
     */
    private int $ttl;

    private string $separator = '-';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $config = $container->get(ConfigInterface::class);
        $this->configs = $config->get('distribute_ws', []);
        $this->server_id = $this->configs['server_id'];
        $this->user_fd_key = $this->configs['user_relate_fd_key'];
        $this->fd_user_key = $this->configs['fd_relate_user_key'];
        $this->ttl = $this->configs['ttl'];
        $this->redis = $container->get(RedisFactory::class)->get($config->get('redis.pool') ?? 'default');
    }

    /**
     * Notes: 生成分布式FD
     * User: 陈朋
     * DateTime: 2022/6/30 下午4:03
     * @param int $fd
     * @return DistributeServerFD
     */
    public function genDistributeFd(int $fd): DistributeServerFD
    {
        $distribute_fd = $this->server_id . $this->separator . $fd;
        return make(DistributeServerFD::class, [$distribute_fd, $this->server_id, $fd]);
    }

    /**
     * Notes: 格式化
     * User: 陈朋
     * DateTime: 2022/6/30 下午4:19
     * @param $format
     * @param ...$value
     * @return string
     */
    private function formatKey($format, ...$value): string
    {
        return sprintf($format, ...$value);
    }

    /**
     * Notes: 客户端fd与用户ID绑定关系 返回用户的分布式FD
     * User: 陈朋
     * DateTime: 2021/11/9 18:24
     * @param int $fd
     * @param int $user_id
     * @return DistributeServerFD
     */
    public function bindRelation(int $fd, int $user_id): DistributeServerFD
    {
        $distributed_fd = $this->genDistributeFd($fd);
        $this->redis->setex($this->formatKey($this->fd_user_key, $distributed_fd->toString()), $this->ttl, $user_id);
        $this->redis->setex($this->formatKey($this->user_fd_key, $user_id), $this->ttl, $distributed_fd->toString());
        return $distributed_fd;
    }

    /**
     * Notes: 解除指定的客户端fd与用户绑定关系
     * User: 陈朋
     * DateTime: 2021/11/9 16:57
     * @param string $fd
     */
    public function removeRelation(string $fd): void
    {
        $redis = $this->redis;
        $user_id = $this->findFdUserId($fd);
        if (empty($user_id)) {
            return;
        }
        $redis->del(
            $this->getUserFDKey($user_id),
            $this->getFdUserKey($fd)
        );
    }

    /**
     * Notes: 查询客户端fd对应的用户ID
     * User: 陈朋
     * DateTime: 2021/11/9 16:57
     * @param string $fd
     * @return int
     */
    public function findFdUserId(string $fd): int
    {
        return (int)$this->redis->get($this->getFdUserKey($fd));
    }


    /**
     * Notes: 获取用户的FD
     * User: 陈朋
     * DateTime: 2021/11/9 17:05
     * @param int $user_id
     * @return DistributeServerFD | null
     */
    public function findUserFd(int $user_id): ?DistributeServerFD
    {
        $distribute_fd = (string)$this->redis->get($this->getUserFDKey($user_id));
        if (!$distribute_fd) {
            return null;
        }
        return make(DistributeServerFD::class, [$distribute_fd, ...$this->parseDistributeFD($distribute_fd)]);
    }

    /**
     * Notes: 解析分布式fd为server_id 和 该server 的fd
     * User: 陈朋
     * DateTime: 2022/6/30 下午4:36
     * @param string $fd
     * @return array
     */
    public function parseDistributeFD(string $fd): array
    {
        $parsed = explode($this->separator, $fd);
        $server_fd = (int)array_pop($parsed);
        return [implode($this->separator, $parsed), $server_fd];
    }

    /**
     * Notes: 获取用户的fd所在key
     * User: 陈朋
     * DateTime: 2021/11/12 17:37
     * @param string $fd
     * @return string
     */
    private function getFdUserKey(string $fd): string
    {
        return $this->formatKey($this->fd_user_key, $fd);
    }

    /**
     * Notes: 获取用户的fd所在key
     * User: 陈朋
     * DateTime: 2021/11/12 17:37
     * @param int $uid
     * @return string
     */
    private function getUserFDKey(int $uid): string
    {
        return $this->formatKey($this->user_fd_key, $uid);
    }

    /**
     * Notes: 获取serverID
     * User: 陈朋
     * DateTime: 2022/6/30 下午5:28
     * @return string
     */
    public function getServerId(): string
    {
        return $this->server_id;
    }
}
