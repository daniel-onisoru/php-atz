<?php
namespace PhpAtz;

class Modem extends Base
{

    function atz()
    {
        $this->serial->write("ATZ\n");

        return $this->serial->read_ok();
    }

}