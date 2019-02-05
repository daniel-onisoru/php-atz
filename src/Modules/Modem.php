<?php
/**
* Modem Module
*
* Modem initialization, configuration, setup
*
* @author Daniel Onisoru <daniel.onisoru@gmail.com>
* @project PhpAtz
*/

namespace PhpAtz\Modules;

class Modem extends \PhpAtz\Utils\Base
{

    /**
    * Get modem attention and reset to default state
    *
    * @returns bool
    */
    function atz()
    {
        $this->conn->write("ATZ\n");
        return $this->conn->read_ok();
    }

}