<?php
/**
* SMS Module
*
* Get, Send SMS messages, Manage SMS memory etc.
*
* @author Daniel Onisoru <daniel.onisoru@gmail.com>
* @project PhpAtz
*/

namespace PhpAtz\Modules;

class Sms extends \PhpAtz\Utils\Base
{
    /**
    * @ignore
    */
    function __construct($phpatz)
    {
        parent::__construct($phpatz);

        // make sure the modem is in proper mode
        $this->setMode($this->config['sms_mode']);
    }

    /**
    * Send a SMS messages
    *
    * @param string $phone
    * @param string $message
    * @returns mixed message ref or false on error
    */
    function send($phone, $message)
    {
        if ($this->config['sms_mode'] == 'text')
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
        elseif ($this->config['sms_mode'] == 'pdu')
        {

        }

        return false;
    }

    /**
    * Returns messages stored in active memory
    *
    * @param string $list - all,old,new(default)
    * @returns array messages
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

    /**
    * Returns active storage info.
    *
    * @returns array
    */
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

    /**
    * Sets SMS mode to text or pdu
    *
    * @param mixed $mode - text,pdu
    * @returns bool
    */
    function setMode($mode)
    {
        $this->phpatz->config['sms_mode'] = $mode;

        $this->conn->write("AT+CMGF=" . ($mode == 'text' ? 1: 0) . "\n");
        return $this->conn->read_ok();
    }

}
