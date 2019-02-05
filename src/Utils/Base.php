<?php
/**
* Base Class
*
* Helper class that most other classes extend.
* Usefull for defining magic methods, helper functions etc
*
* @author Daniel Onisoru <daniel.onisoru@gmail.com>
* @project PhpAtz
*/

namespace PhpAtz\Utils;

class Base
{
    /**
    * @ignore
    */
    function __construct($phpatz)
    {
        $this->phpatz = $phpatz;
    }

    /**
    * @ignore
    */
    function __get($v)
    {
        return  $this->phpatz->{$v};
    }

    /**
    * @ignore
    */
    function __set($k, $v)
    {
        if ($k == 'phpatz') $this->{$k} = $v;
        return  $this->phpatz->{$k} = $v;
    }

    /**
    * @ignore
    */
    function __call($v, $p)
    {
        return call_user_func_array([$this->phpatz, $v], $p);
    }
}