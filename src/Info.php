<?php
namespace PhpAtz;

class Info extends Base
{

    function getSignal()
    {
        $this->serial->write("AT+CSQ\n");

        $reply = $this->serial->read();

        if (!$reply) return false;

        foreach ($reply as $line)
        {
            if (preg_match('/\+CSQ: ([0-9\,]+)/', $line, $matches))
            {
                return floatval(str_replace(',', '.',$matches[1]));
            }
        }

        return false;
    }

    function getIMEI()
    {
        $this->serial->write("AT+CGSN\n");

        $reply = $this->serial->read();

        if (!$reply) return false;

        foreach ($reply as $line)
        {
            if (preg_match('/([0-9]+)/', $line, $matches))
            {
                return str_replace(',', '.',$matches[1]);
            }
        }

        return false;
    }

    function getNetwork()
    {
        $this->serial->write("AT+COPS=3,0\n");

        if (!$this->serial->read_ok())
            return false;

        $this->serial->write("AT+COPS?\n");
        $reply = $this->serial->read();

        if (!$reply) return false;

        foreach ($reply as $line)
        {
            if (preg_match('/\+COPS: (.+)\,(.+)\,"MCC ([0-9]+) MNC ([0-9]+)"/', $line, $matches))
            {
                if ($matches[1] && $matches[2]) return $matches[1] . ', ' . $matches[2];

                if (file_exists('phpatz/mcc-mnc-table.json'))
                {
                    $mcc = json_decode(file_get_contents('phpatz/mcc-mnc-table.json'), true);

                    foreach ($mcc as $code)
                    {
                        if ($code['mcc'] == $matches[3] && $code['mnc'] == $matches[4])
                            return $code['network'] .', ' . $code['country'];
                    }
                }

                return 'unknown, unknown';
            }
        }

        return false;
    }

}