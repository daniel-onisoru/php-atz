<?php
/**
* Tcp Adapter
*
* Use to communicate with modems over tcp
*
* @author Daniel Onisoru <daniel.onisoru@gmail.com>
* @project PhpAtz
*/

namespace PhpAtz\Adapters;


class Tcp extends \PhpAtz\Adapters\Serial
{

    /**
    * @ignore
    */
    function __construct($phpatz)
    {
        parent::__construct($phpatz);
        $this->open($phpatz->config);
    }

    /**
    * open connection
    *
    * @param array $config - use to overwrite stored configuration
    * @returns socket
    */
    function open($config = false)
    {
        if (!$config) $config &= $this->config;

        list ($host, $port) = explode(':', $config['device']);
        $this->sock = @fsockopen($host, $port, $errno, $errstr, $this->config['timeout']);
        if (!$this->sock) {
            throw new \Exception($errstr, $errno);
        }

        stream_set_timeout($this->sock, $this->config['timeout']);

        return $this->sock;
    }

    /**
    * close connection
    *
    * @returns bool
    */
    function close()
    {
        if ($this->sock)
            return fclose($this->sock);
    }
}