<?php

declare(strict_types=1);

namespace Aston\DistributeWs\Driver;

use Hyperf\AsyncQueue\Exception\InvalidDriverException;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class MultiRedisDriverFactory extends DriverFactory
{
    /**
     * 基于local的配置模版根据传入的channel名字动态生成driver 实现动态的消息队列生成
     * @throws InvalidDriverException when the driver invalid
     */
    public function get(string $name): DriverInterface
    {
        $driver = $this->drivers[$name] ?? null;

        if (!$driver instanceof DriverInterface) {
            if ($this->configs["local"]) {
                $item = $this->configs["local"];
                $driverClass = $item['driver'];
                if (!class_exists($driverClass)) {
                    throw new InvalidDriverException(sprintf('[Error] class %s is invalid.', $driverClass));
                }
                $item["channel"] = $name;
                $driver = make($driverClass, ['config' => $item]);
                if (!$driver instanceof DriverInterface) {
                    throw new InvalidDriverException(sprintf('[Error] class %s is not instanceof %s.', $driverClass, DriverInterface::class));
                }
                $this->drivers[$name] = $driver;
            }
        }
        return $driver;
    }
}
