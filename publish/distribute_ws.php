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
return [
    'user_relate_fd_key' => 'user:relate:fd:%s',//用户ID与分布式FD关联key
    'fd_relate_user_key' => 'fd:relate:user:%s',//分布式FD与用户ID关联key
    'ttl' => 7200,//key的过期时间,
    'default_opcode' => WEBSOCKET_OPCODE_BINARY,
    'server_id' => env('DISTRIBUTE_SERVER_ID', uniqid()),//服务器ID，分布式部署时保证每台服务器的SERVER_ID不同即可
];
