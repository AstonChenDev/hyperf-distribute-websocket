<?php

namespace Aston\DistributeWs;


class DistributeMsg
{
    private string $msg;
    private int $fd;

    public function __construct(string $msg, int $fd)
    {
        $this->msg = $msg;
        $this->fd = $fd;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }
}