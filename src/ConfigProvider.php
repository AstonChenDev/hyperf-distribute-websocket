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

namespace Aston\DistributeWs;

use Aston\DistributeWs\Contract\ISender;
use Aston\DistributeWs\Contract\ISocketClientService;
use Aston\DistributeWs\Driver\QueueDriver;
use Aston\DistributeWs\Driver\SubscribeDriver;
use Aston\DistributeWs\Implement\SocketClientService;
use Aston\DistributeWs\Process\AsyncQueueConsumer;
use Aston\DistributeWs\Process\SubscribeProcess;
use Hyperf\AsyncQueue\Driver\RedisDriver;

class ConfigProvider
{
    public function __invoke(): array
    {
        $config = [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for distribute ws.',
                    'source' => __DIR__ . '/../publish/distribute_ws.php',
                    'destination' => BASE_PATH . '/config/autoload/distribute_ws.php',
                ],
            ],
        ];
        $path = file_exists($config['publish'][0]['destination']) ? $config['publish'][0]['destination'] : $config['publish'][0]['source'];
        $custom_conf = require_once $path;
        $driver = $custom_conf['driver'] ?? QueueDriver::class;
        $server_id = $custom_conf['server_id'] ?? uniqid();
        $queue_config = $custom_conf['queue_config'] ?? [
                'process_num' => 1,
                'process_concurrent_limit' => 10
            ];

        switch ($driver) {
            case QueueDriver::class:
                $config['async_queue']['local'] = [
                    'driver' => RedisDriver::class,
                    'redis' => [
                        'pool' => 'default'
                    ],
                    'channel' => $server_id,
                    'timeout' => 2,
                    'retry_seconds' => 5,
                    'handle_timeout' => 10,
                    'processes' => $queue_config['process_num'],
                    'concurrent' => [
                        'limit' => $queue_config['process_concurrent_limit']
                    ],
                ];
                $processes = AsyncQueueConsumer::class;
                break;
            case SubscribeDriver::class:
                $processes = SubscribeProcess::class;
                break;
            default:
                throw new \Exception($driver . ' is not supported');
        }
        $config['processes'] = [
            $processes
        ];
        $config['dependencies'] = [
            ISocketClientService::class => SocketClientService::class,
            ISender::class => $driver
        ];
        return $config;
    }
}
