<?php
namespace PhpAtz;

class PhpAtz
{
    public $config = [
        'device'    => null,
        'adapter'   => 'Serial',
        'auto_init' => true,

        'sms_mode'  => 'pdu',

        'timeout'   => 5,
        'debug'     => false
    ];

    public $sock = false;

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

    public function __destruct()
    {
        if ($this->sock)
            $this->conn->close();
    }

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