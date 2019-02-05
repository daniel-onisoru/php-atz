<?php
namespace PhpAtz\Modules;

class Sms extends \PhpAtz\Utils\Base
{
    function __construct($phpatz)
    {
        parent::__construct($phpatz);

        $this->setMode($this->config['sms_mode']);
    }

    function send($phone, $message)
    {

        switch ($this->config['sms_mode'])
        {
            case 'text':
                $this->conn->write("AT+CMGS=\"$phone\"\n");
                $this->conn->read_line();
                $this->conn->write($message);
                $this->conn->write(chr(26));

                $reply = $this->conn->read();

                if (!$reply) return false;

                foreach ($reply as $line)
                {
                    if (preg_match('/\+CMGS: ([0-9]+)/', $line, $matches))
                    {
                        return intval(str_replace(',', '.',$matches[1]));
                    }
                }

                return false;
            case 'pdu':
                return $this->_send_pdu($phone, $message);
            default:
                throw new \Exception('sms_mode not set.');
        }
    }

    /*function getNew()
    {
        $this->conn->write("AT+CMGL=\"REC UNREAD\"\n");
        $reply = $this->conn->read();

        var_dump($reply);
    }*/

    /**
    * returns messages stored in active memory
    *
    * @param mixed $list - all,old,new(default)
    */
    function getReceived($stat = 'new')
    {
        if ($this->config['sms_mode'] == 'text')
            throw new \Exception('Reading messages in text mode is not implemented.');

        switch ($stat)
        {
            case 'old':     $stat = 1; break;
            case 'new':     $stat = 0; break;
            default: $stat = 4;
        }

        $this->conn->write("AT+CMGL=$stat\n");

        $reply = $this->conn->read();

        $messages       = [];
        $mp_messages    = [];

        $msg = false;
        foreach ($reply as $line)
        {
            if (preg_match('/\+CMGL: ([0-9]+),([0-9]+),(.*),([0-9]+)/', $line, $matches))
            {
                $msg = [
                    'mem_index'     => $matches[1],
                    'status'        => $matches[2] ? 'read' : 'unread',
                    'sender_name'   => $matches[3],
                    'pdu_length'    => $matches[4]
                ];

                continue;
            }

            if ($msg && preg_match('/([a-z0-9]+)/i', $line, $matches))
            {
                $pdu = $matches[1];
                $decoded = $this->pdu->decode($pdu);

                // if it's multipart message we have some concatenating to do
                if (isset($decoded['udh']) && isset($decoded['udh']['multipart']) && $decoded['udh']['multipart'])
                {
                    $ref = $decoded['udh']['multipart']['ref'];

                    if (!isset($mp_messages[$ref]))
                    {
                        $mp_messages[$ref] = [
                            'mem_index'     => [$msg['mem_index']],
                            'status'        => $msg['status'],
                            'sender_name'   => $msg['sender_name'],
                            'sender'        => $decoded['sender']['number'],
                            'date_received' => $decoded['scts'],
                            'parts_total'   => $decoded['udh']['multipart']['total'],
                            'parts'         => [
                                $decoded['udh']['multipart']['current'] => $decoded['message']
                            ]
                        ];
                    } else {
                        $mp_messages[$ref]['mem_index'][]   = $msg['mem_index'];
                        $mp_messages[$ref]['parts'][$decoded['udh']['multipart']['total']] = $decoded['message'];

                        if ($mp_messages[$ref]['parts_total'] == count($mp_messages[$ref]['parts']))
                        {
                            $mp_messages[$ref]['message'] = implode('', $mp_messages[$ref]['parts']);
                            unset($mp_messages[$ref]['parts_total']);
                            unset($mp_messages[$ref]['parts']);

                            $messages[] = $mp_messages[$ref];

                            unset($mp_messages[$ref]['parts']);
                        }
                    }

                } else {
                    $messages[] = [
                        'mem_index'     => [$msg['mem_index']],
                        'status'        => $msg['status'],
                        'sender_name'   => $msg['sender_name'],
                        'sender'        => $decoded['sender']['number'],
                        'date_received' => $decoded['scts'],
                        'message'       => $decoded['message']
                    ];
                }
            }

            $msg = false;
        }

        return $messages;
    }

    function getStorageStatus()
    {
        $this->conn->write("AT+CPMS?\n");
        $reply = $this->conn->read();

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
        $this->conn->write("AT+CMGF=" . ($mode == 'text' ? 1: 0) . "\n");
        return $this->conn->read_ok();
    }

}