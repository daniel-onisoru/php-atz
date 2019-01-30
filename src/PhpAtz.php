<?php
namespace PhpAtz;

class PhpAtz
{
    public $config = [
        'device'    => null,
        'adapter'   => false,

        'sms_mode'  => 'text', //pdu

        'timeout'   => 5,
        'debug'     => false
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);

        register_shutdown_function([$this, '_shutdown']);
        spl_autoload_register([$this, '_autoload']);

        return;

        switch ($config['adapter'])
        {
            case 'tcp':
                $this->serial = new SerialTcp($this);
                break;
            default:
                $this->serial = new Serial($this);
                break;
        }

        if (!$this->modem->atz())
            throw new \Exception('ATZ failed.');
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

    public static function _shutdown()
    {

    }

    public function __get($v)
    {

        $v = strtolower($v);
        if (file_exists('phpatz/' . ucfirst($v) . '.php'))
        {
            $className = 'PhpAtz\\' . ucfirst($v);
            $this->{$v} = new $className($this);

            return $this->{$v};
        }


        //var_dump(getcwd() . '/phpatz/' . ucfirst($v) . '.php', )

    }
}