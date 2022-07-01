<?php

namespace Aston\DistributeWs;


class DistributeMsg
{
    private $msg;
    private int $fd;
    private int $opcode;

    public function __construct($msg, int $fd, int $opcode)
    {
        $this->msg = $msg;
        $this->fd = $fd;
        $this->opcode = $opcode;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @return int
     */
    public function getOpcode(): int
    {
        return $this->opcode;
    }
}