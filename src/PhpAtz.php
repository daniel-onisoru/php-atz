<?php
/**
* PhpAtz Main class
*
*
* @author Daniel Onisoru <daniel.onisoru@gmail.com>
* @project PhpAtz
*/

namespace PhpAtz;

class PhpAtz
{
    /**
    * @ignore
    * @var array
    */
    public $config = [
        'device'    => null,
        'adapter'   => 'Serial',
        'auto_init' => true,

        'sms_mode'  => 'pdu',

        'timeout'   => 5,
        'debug'     => false
    ];

    /**
    * @ignore
    */
    public $sock = false;

    /**
    * @ignore
    * @param mixed $config
    */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        spl_autoload_register([$this, '_autoload']);

        if ($this->config['auto_init'])
            $this->init();
    }

    public function init()
    {
        $adapter = 'PhpAtz\\Adapters\\' . ucfirst($this->config['adapter']);
        $this->conn = new $adapter($this);

        if (!$this->modem->atz())
            throw new \Exception('ATZ failed.');

        return $this;
    }

    /**
    * @ignore
    */
    public function __destruct()
    {
        if ($this->sock)
            $this->conn->close();
    }

    /**
    * @ignore
    * @param mixed $className
    */
    public static function _autoload($className)
    {
        if ((class_exists($className, FALSE)) || (strpos($className, 'PhpAtz') !== 0)) {
            return false;
        }

        $path = str_replace(['PhpAtz\\', '\\'], ['', '/'], $className) . '.php';

        if (is_file('phpatz/' . $path) && is_readable('phpatz/' . $path))
        {
            require_once 'phpatz/' . $path;
            return true;
        }

        return false;
    }

    /**
    * @ignore
    * @param mixed $v
    */
    public function __get($v)
    {
        if (file_exists(dirname(__FILE__) . '/Modules/' . ucfirst($v) . '.php'))
        {
            $className = 'PhpAtz\\Modules\\' . ucfirst($v);
            $this->{$v} = new $className($this);
        }

        if (file_exists(dirname(__FILE__) . '/Utils/' . ucfirst($v) . '.php'))
        {
            $className = 'PhpAtz\\Utils\\' . ucfirst($v);
            $this->{$v} = new $className($this);
        }

        return $this->{$v};
    }
}