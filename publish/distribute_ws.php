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
    'user_relate_fd_key' => 'user:relate:fd:%s',
    'fd_relate_user_key' => 'fd:relate:user:%s',
    'ttl' => 3600,
    'server_id' => env('DISTRIBUTE_SERVER_ID', uniqid()),
];
