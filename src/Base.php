<?php
namespace PhpAtz;


class Base
{
    function __construct($phpatz)
    {
        $this->phpatz = $phpatz;
    }

    function __get($v)
    {
        return  $this->phpatz->{$v};
    }

    function __set($k, $v)
    {
        if ($k == 'phpatz') $this->{$k} = $v;
        return  $this->phpatz->{$k} = $v;
    }

    function __call($v, $p)
    {
        return call_user_func_array([$this->phpatz, $v], $p);
    }
}