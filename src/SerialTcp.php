<?php
namespace PhpAtz;


class SerialTcp extends Serial
{

    function __construct($phpatz)
    {
        parent::__construct($phpatz);
        $this->open($phpatz->config);
    }

    function open($config)
    {
        list ($host, $port) = explode(':', $config['device']);
        $this->sock = @fsockopen($host, $port, $errno, $errstr, $this->config['timeout']);
        if (!$this->sock) {
            throw new \Exception($errstr, $errno);
        }

        stream_set_timeout($this->sock, $this->config['timeout']);
    }

    function close()
    {
        if ($this->sock)
            fclose($this->sock);
    }


}