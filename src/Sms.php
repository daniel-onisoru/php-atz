<?php
namespace PhpAtz;

class Sms extends Base
{
    function __construct($phpatz)
    {
        parent::__construct($phpatz);

        return;
        $this->setMode($this->config['sms_mode']);
    }

    function send($phone, $message)
    {

        switch ($this->config['sms_mode'])
        {
            case 'text':
                return $this->_send_text($phone, $message);
            case 'text':
                return $this->_send_pdu($phone, $message);
            default:
                throw new \Exception('sms_mode not set.');
        }
    }

    function getNew()
    {
        $this->serial->write("AT+CMGL=\"REC UNREAD\"\n");
        $reply = $this->serial->read();

        var_dump($reply);
    }

    function getReceived()
    {
        $this->serial->write("AT+CMGL=4\n");
        $reply = $this->serial->read();

        $messages = [];
        $msg = false;
        foreach ($reply as $line)
        {
            if (preg_match('/\+CMGL: ([0-9]+),([0-9]+),(.*),([0-9]+)/', $line, $matches))
            {
                $msg = [
                    'index'     => $matches[1],
                    'status'    => $matches[2] ? 'read' : 'unread',
                    'sender'    => $matches[3],
                    'pdu_length' => $matches[4]
                ];

                continue;
            }

            if ($msg && preg_match('/([a-z0-9]+)/i', $line, $matches))
            {
                $msg['pdu'] = $matches[1];
                $decoded = $this->pdu->decode($matches[1]);

                $messages[] = $msg;
            }

            $msg = false;
        }

        return $messages;
    }

    function getStorageStatus()
    {
        $this->serial->write("AT+CPMS?\n");
        $reply = $this->serial->read();

        if (!$reply) return false;

        foreach ($reply as $line)
        {
            if (preg_match('/\+CPMS:/', $line) && preg_match_all('/("([a-z]+)",([0-9]+),([0-9]+))/i', $line, $matches))
            {
                return [
                    'read' => [
                        'type' => $matches[2][0],
                        'used' => $matches[3][0],
                        'total' => $matches[4][0],
                    ],
                    'write' => [
                        'type' => $matches[2][1],
                        'used' => $matches[3][1],
                        'total' => $matches[4][1],
                    ],
                    'store' => [
                        'type' => $matches[2][2],
                        'used' => $matches[3][2],
                        'total' => $matches[4][2],
                    ],
                ];
            }
        }

        return false;

    }

    function setMode($mode)
    {
        $this->serial->write("AT+CMGF=" . ($mode == 'text' ? 1: 0) . "\n");
        return $this->serial->read_ok();
    }

    private function _send_text($phone, $message)
    {
        $this->serial->write("AT+CMGF=1\n");

        if (!$this->serial->read_ok())
            return false;

        $this->serial->write("AT+CMGS=\"$phone\"\n");
        $this->serial->write($message. "\n");
        $this->serial->write(chr(26));

        $reply = $this->serial->read();

        if (!$reply) return false;

        foreach ($reply as $line)
        {
            if (preg_match('/\+CMGS: ([0-9]+)/', $line, $matches))
            {
                return intval(str_replace(',', '.',$matches[1]));
            }
        }

        return false;
    }

}