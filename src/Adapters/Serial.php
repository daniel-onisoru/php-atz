<?php
/**
* Serial Adapter
*
* Use to communicate with modems over serial connection
*
* @author Daniel Onisoru <daniel.onisoru@gmail.com>
* @project PhpAtz
*/

namespace PhpAtz\Adapters;


class Serial extends \PhpAtz\Utils\Base
{
    public $log = [];

     /**
    * open connection
    *
    * @param array $config - use to overwrite stored configuration
    * @returns socket
    */
    function open($config)
    {
        throw new \Exception('Serial/open not implemented.');
    }

    /**
    * close connection
    *
    * @returns bool
    */
    function close()
    {
        throw new \Exception('Serial/close not implemented.');
    }

    /**
    * send message to modem
    *
    * @param string $msg
    * @returns mixed bytes written or false on error
    */
    public function write($msg)
    {
        if (!$this->sock)
            throw new \Exception('Device not connected.');

        if ($this->config['debug'])
            $this->log[] = 'W: ' . trim($msg);

        return fwrite($this->sock, $msg);
    }

    /**
    * read modem reply
    *
    * @returns array lines read from modem or false on error
    */
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

            if (preg_match("/^OK|ERROR\r\n$/", $line))
                return $response;
        }

        return false;
    }

    /**
    * checks if last line read is an OK message
    * usefull for commands that only returns OK/ERROR
    *
    * @returns bool
    */
    public function read_ok()
    {
        $reply = $this->read();
        if (preg_match("/^OK\r\n$/", $reply[count($reply) - 1]))
            return true;

        return false;
    }
}
