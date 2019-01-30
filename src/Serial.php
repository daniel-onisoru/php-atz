<?php
namespace PhpAtz;


class Serial extends Base
{
    public $log = [];

    public function write($msg)
    {
        if (!$this->sock)
            throw new \Exception('Device not connected.');

        if ($this->config['debug'])
            $this->log[] = 'W: ' . trim($msg);

        return fwrite($this->sock, $msg);
    }

    public function read()
    {
        if (!$this->sock)
            throw new \Exception('Device not connected.');

        $response = [];

        while(($line = fgets($this->sock)) !== false)
        {
            $response[] = $line;

            if ($this->config['debug'])
                $this->log[] = 'R: ' . trim($line);

            if (preg_match('/OK|ERROR/', $line))
                return $response;
        }

        return false;
    }

    public function read_ok()
    {
        $reply = $this->read();

        if (preg_match('/OK/', $reply[count($reply) - 1]))
            return true;

        return false;
    }
}