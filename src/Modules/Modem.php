<?php
namespace PhpAtz\Modules;

class Modem extends \PhpAtz\Utils\Base
{

    function atz()
    {
        $this->conn->write("ATZ\n");
        return $this->conn->read_ok();
    }

}