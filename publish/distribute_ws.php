<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Aston\DistributeWs\Driver\QueueDriver;

return [
    'user_relate_fd_key' => 'user:relate:fd:%s',//用户ID与分布式FD关联key
    'fd_relate_user_key' => 'fd:relate:user:%s',//分布式FD与用户ID关联key
    'ttl' => 7200,//key的过期时间,
    'driver' => QueueDriver::class,// Aston\DistributeWs\Driver\QueueDriver::class |  Aston\DistributeWs\Driver\SubscribeDriver::class,
    'queue_config' => [
        'process_num' => (int)env('LOCAL_PUSH_PROCESS_NUM', 1),
        'process_concurrent_limit' => (int)env('LOCAL_PUSH_PROCESS_CONCURRENT_LIMIT', 10)
    ],
    'default_opcode' => WEBSOCKET_OPCODE_BINARY,
    'server_id' => (string)env('DISTRIBUTE_SERVER_ID', uniqid()),//服务器ID，分布式部署时保证每台服务器的SERVER_ID不同即可
];
