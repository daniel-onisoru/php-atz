<?php
/**
* PDU Utils
*
* PDU decoder, encoder
*
* @author Daniel Onisoru <daniel.onisoru@gmail.com>
* @project PhpAtz
*/

namespace PhpAtz\Utils;

// dcs encoding
define('DCS_GSM_7bit', 'GSM 7 bit');
define('DCS_8bit', '8 bit data');
define('DCS_UCS2', 'UCS2');

//message class
define('CLASS_0', 'Class 0 (Flash message)');
define('CLASS_1', 'Class 1 (ME-specific)');
define('CLASS_2', 'Class 2 (SIM/USIM-specific)');
define('CLASS_3', 'Class 3 (TE-specific)');

class Pdu extends \PhpAtz\Utils\Base
{

    private $ToN = [
        0b00000000 => 'Unknown 1',
        0b00010000 => 'International number 2)',
        0b00100000 => 'National number 3)',
        0b00110000 => 'Network specific number 4)',
        0b01000000 => 'Subscriber number 5)',
        0b01010000 => 'Alphanumeric, (coded according to 3GPP TS 23.038 [9] GSM 7-bit default alphabet)',
        0b01100000 => 'Abbreviated number',
        0b01110000 => 'Reserved for extension',
    ];

    private $NBI = [
        0b00000000 => 'Unknown',
        0b00000001 => 'ISDN/telephone numbering plan (E.164/E.163)',
        0b00000011 => 'Data numbering plan (X.121)',
        0b00000100 => 'Telex numbering plan',
        0b00000101 => 'Service Centre Specific plan 1)',
        0b00000110 => 'Service Centre Specific plan 1)',
        0b00001000 => 'National numbering plan',
        0b00001001 => 'Private numbering plan',
        0b00001010 => 'ERMES numbering plan (ETSI DE/PS 3 01 3)',
        0b00001111 => 'Reserved for extension',
    ];

    private $dict_7bit = [
        0 => '@', 1 => '£', 2 => '$', 3 => '¥', 4 => 'è', 5 => 'é', 6 => 'ù', 7 => 'ì', 8 => 'ò', 9 => 'Ç',
        10 =>'\n', 11 => 'Ø', 12 => 'ø', 13 => '\r', 14 => 'Å', 15 => 'å', 16 => '\u0394', 17 => '_', 18 => '\u03a6', 19 => '\u0393',
        20 => '\u039b', 21 => '\u03a9', 22 => '\u03a0', 23 => '\u03a8', 24 => '\u03a3', 25 => '\u0398', 26 => '\u039e', 28 => 'Æ', 29 => 'æ',
        30 => 'ß', 31 => 'É', 32 => ' ', 33 => '!', 34 => '"', 35 => '#', 36 => '¤', 37 => '%', 38 => '&', 39 => '\'',
        40 => '(', 41 => ')', 42 => '*', 43 => '+', 44 => ',', 45 => '-', 46 => '.', 47 => '/', 48 => '0', 49 => '1',
        50 => '2', 51 => '3', 52 => '4', 53 => '5', 54 => '6', 55 => '7', 56 => '8', 57 => '9', 58 => ' =>', 59 => ';',
        60 => '<', 61 => '=', 62 => '>', 63 => '?', 64 => '¡', 65 => 'A', 66 => 'B', 67 => 'C', 68 => 'D', 69 => 'E',
        70 => 'F', 71 => 'G', 72 => 'H', 73 => 'I', 74 => 'J', 75 => 'K', 76 => 'L', 77 => 'M', 78 => 'N', 79 => 'O',
        80 => 'P', 81 => 'Q', 82 => 'R', 83 => 'S', 84 => 'T', 85 => 'U', 86 => 'V', 87 => 'W', 88 => 'X', 89 => 'Y',
        90 => 'Z', 91 => 'Ä', 92 => 'Ö', 93 => 'Ñ', 94 => 'Ü', 95 => '§', 96 => '¿', 97 => 'a', 98 => 'b', 99 => 'c',
        100 => 'd', 101 => 'e', 102 => 'f', 103 => 'g', 104 => 'h', 105 => 'i', 106 => 'j', 107 => 'k', 108 => 'l', 109 => 'm',
        110 => 'n', 111 => 'o', 112 => 'p', 113 => 'q', 114 => 'r', 115 => 's', 116 => 't', 117 => 'u', 118 => 'v', 119 => 'w',
        120 => 'x', 121 => 'y', 122 => 'z', 123 => 'ä', 124 => 'ö', 125 => 'ñ', 126 => 'ü', 127 => 'à'
    ];

    // escape char = 27
    private $dict_7bit_esc = [
        10 => '\n',
        20 => '^', 40 => '{', 41 => '}', 47 => '\\',
        60 => '[', 61 => '~', 62 => ']', 64 => '|', 101 => '&#8364;'
    ];

    /**
    * decode pdu string
    *
    * @param mixed $pdu
    * @param mixed $dir - 0 = message recieved, 1 = message sent
    * @returns array decoded PDU message or false on error
    */
    public function decode($pdu, $dir = 0)
    {
        /*
        07                           Length of the SMSC information (in this case 7 octets)
        91 04 67 06 00 95 F0         Type-of-address of the SMSC; SMSC Number
        24                           PDU Type
        0B                           Address-Length. Length of the sender number
        91 04 76 82 48 02 F2         Type-of-address of the Sender; Sender Number
        00 00                        PID / DCS
        91 10 92 02 94 23 80         Timestamp
        03                           Msg Length 3
        C8 39 1A                     Msg


        07
        91 04 67 06 00 95 F0
        44
        0B
        91 04 76 82 48 02 F2
        00 00
        91 10 92 12 44 74 80
        A0                          Msg Length 140
        05 00 03 C2 02 01           UDH, IEI 00, IEILength 03, ref, total, current
        9064F45C3D17CF41F539685E9F83E6F539685E07CDEB73D0BC3E07D9C3A0791A44B6CF41E53A886C9F83EC61903E3D0785D361903E3D07A9F5E2B97A0ED2A7E7A0721D144E8741E17418640F83C2F61C1A7406BDC7E834687D06A5DDA0371A644E8340693728ED06A1416B50DA0D7AA341EB33687D06A141A035080402ADCFA035687D069D41
        */

        $decoded = [];
        $octets = str_split($pdu, 2);

        // smsc part
        $smscLength = array_splice($octets, 0, 1);
        $smscLength =  intval($smscLength[0], 16);
        if ($smscLength) {
            $decoded['smsc'] = ['format' => [], 'number' => ''];
            $decoded['smsc']['format'] = $this->_toa_decode(array_splice($octets, 0, 1)[0]);
            $decoded['smsc']['number'] = $this->_number(array_splice($octets, 0, $smscLength - 1), $smscLength, $decoded['smsc']['format']);
        }

        //PDU Type
        $decoded['type'] = $this->_pdu_type(array_splice($octets, 0, 1)[0], $dir);

        switch ($decoded['type']['mti'])
        {
            case 'SMS-DELIVER':
                $this->_decode_deliver($octets, $decoded);
                break;
        }

        return $decoded;
    }

    function encode($phone, $message)
    {
        if (!preg_match('/^\+?([0-9]+)$/', $phone))
            throw new \Exception('Invalid phone number format.');

        if (!$message)
            throw new \Exception('Message cannot be empty.');


        $udh = false;

        // use phone smsc
        $pdu[] = '00';

        $tpdu = 0b00010001; // TP-VPF relative
        if ($udh) $tpdu |= 0b01000000; // UDHI pressent
        $pdu[] = dechex($tpdu);

        $pdu[] = '00'; //message reference number


        $phoneNo = preg_replace('/[^0-9]*/', '', $phone);
        $phoneLength = strlen($phoneNo);

        $pdu[] = str_pad(strtoupper(dechex($phoneLength)), 2, '0', STR_PAD_LEFT); // Length of phone number
        $pdu[] = $this->_toa_encode($phone);

        if ($phoneLength % 2) $phoneNo .= 'F';
        for ($i = 0; $i < $phoneLength; $i+=2)
        {
            $pdu[] = substr($phoneNo, $i+1, 1) . substr($phoneNo, $i, 1);
        }

        $dcs = DCS_GSM_7bit;

        $pdu[] = '00'; // Protocol IDentifier
        $pdu[] = $this->_dcs_encode($dcs, CLASS_0);

        $pdu[] = 'FF'; //validity period 4 days

        //encode user data
        switch ($dcs)
        {
            case DCS_GSM_7bit:
                $ud = $this->_7bit_encode($message);
                break;
            case DCS_8bit:
                throw new \Exception('8bit encoding not implemented.');
                break;
            case DCS_UCS2:
                $ud = $this->_ucs2_encode($message);
                break;
        }


        $pdu[] = dechex(strlen($ud) / 2); // udl
        $pdu[] = $ud; //ud

        return implode('', $pdu);
    }

    /**
    * decodes SMS-DELIVER message
    * @ignore
    *
    * @param mixed $octets
    * @param mixed $decoded
    */
    private function _decode_deliver(&$octets, &$decoded)
    {
        // sender address
        $senderLength = array_splice($octets, 0, 1);
        $senderLength = intval($senderLength[0], 16);
        if ($senderLength) {
            $decoded['sender'] = ['format' => [], 'number' => ''];

            $numberLength = ceil($senderLength / 2);
            $decoded['sender']['format'] = $this->_toa_decode(array_splice($octets, 0, 1)[0]);
            $decoded['sender']['number']  = $this->_number(array_splice($octets, 0, $numberLength), $numberLength + 1, $decoded['sender']['format']);
        }

        // pid & dcs
        $decoded['pid'] = $this->_pid(array_splice($octets, 0, 1)[0]);
        list ($decoded['dcs'], $decoded['class']) = $this->_dcs_decode(array_splice($octets, 0, 1)[0]);

        // timestamp
        $decoded['scts'] = $this->_scts(array_splice($octets, 0, 7));

        // ud length
        $decoded['length'] = intval(array_splice($octets, 0, 1)[0], 16);

        $ud = $octets;
        // udh
        if ($decoded['type']['udhi'])
        {
            $decoded['udh'] = [
                'length'    => intval(array_splice($octets, 0, 1)[0], 16)
            ];

            $k = 0;
            while ($k < $decoded['udh']['length'])
            {
                $iei    = intval(array_splice($octets, 0, 1)[0], 16);
                $iedl   = intval(array_splice($octets, 0, 1)[0], 16);

                $k = $k + 2 + $iedl;

                if ($iei == 0 || $iei == 8)
                {
                    $decoded['udh']['multipart'] = [];

                    if ($iei == 8)
                       $decoded['udh']['multipart']['ref'] = intval(array_splice($octets, 0, 1)[0], 16) * 256 + intval(array_splice($octets, 0, 1)[0], 16);
                    else
                       $decoded['udh']['multipart']['ref'] = intval(array_splice($octets, 0, 1)[0], 16);

                    $decoded['udh']['multipart']['total']   = intval(array_splice($octets, 0, 1)[0], 16);
                    $decoded['udh']['multipart']['current'] = intval(array_splice($octets, 0, 1)[0], 16);
                }
            }

        }

        // message
        switch ($decoded['dcs'])
        {
            case DCS_GSM_7bit:
                $skip = $decoded['type']['udhi'] ? floor(((($decoded['udh']['length'] + 1) * 8) + 6) / 7) : 0;
                $decoded['message'] = $this->_7bit_decode($ud, $skip);
                break;
            case DCS_UCS2:
                //$skip = $decoded['type']['udhi'] ? floor(((($decoded['udh']['length'] + 1) * 8) + 6) / 7) : 0;
                $decoded['message'] = $this->_ucs2_decode($ud);
                break;
            default:
                throw new \Exception('DCS: ' . $decoded['dcs'] . ' not impemented');
        }
    }

    /**
    * decodes octets into 7bit alphabet
    *
    * @ignore
    * @param mixed $octets - hex encoded octets
    * @param mixed $skip - chars to skip from the begining (because of udh)
    */
    function _7bit_decode($octets, $skip = 0)
    {
        $septets = $this->_oct2sept($octets);

        $message = '';
        for ($i=0; $i<count($septets); $i++)
        {
            if ($i<$skip) continue;
            $chr = $septets[$i];

            if ($chr != 27)
            {
                $message .= $this->dict_7bit[$chr];
            } else {
                $i++;
                $chr      = $septets[$i];
                $message .= $this->dict_7bit_esc[$chr];
            }
        }

        return $message;
    }

    /**
    * encodes string into 7bit alphabet
    *
    * @ignore
    * @param mixed $octets - hex encoded octets
    * @param mixed $skip - chars to skip from the begining (because of udh)
    */
    function _7bit_encode($string)
    {
        $dict       = array_flip($this->dict_7bit);
        $dict_esc   = array_flip($this->dict_7bit_esc);

        $string     = str_split($string);
        $septets    = [];

        foreach ($string as $char)
        {
            if ($key = array_search($char, $this->dict_7bit))
            {
                $septets[] = str_pad(decbin($key), 7, '0', STR_PAD_LEFT);
                continue;
            }

            if ($key = array_search($char, $this->dict_7bit_esc))
            {
                $septets[] = str_pad(decbin(27), 7, '0', STR_PAD_LEFT);
                $septets[] = str_pad(decbin($key), 7, '0', STR_PAD_LEFT);
                continue;
            }
        }

        return $this->_sept2oct($septets);
    }

    /**
    * decodes UCS2 message
    *
    * @ignore
    * @param mixed $octets
    */
    private function _ucs2_decode($octets)
    {
        $string = implode('', $octets);
        $string = pack("H*", implode('', $octets));

        $string = mb_convert_encoding($string, 'UTF-8', 'UCS-2');
        return $string;
    }

    /**
    * encodes UCS2 string
    *
    * @ignore
    * @param mixed $string
    */
    private function _ucs2_encode($string)
    {
        $string = mb_convert_encoding($string, 'UCS-2', 'UTF-8');
        $string = unpack("H*hex", $string);
        var_dump($string);
        return $string['hex'];
    }

    /**
    * converts octets into septets
    *
    * @ignore
    * @param mixed $octets
    */
    private function _oct2sept($octets)
    {
        array_walk($octets, function (&$oct) {
            $oct = intval($oct, 16);
            $oct = str_pad(decbin($oct), 8, '0', STR_PAD_LEFT);
        });

        $octets = array_reverse($octets);
        $octets = implode('', $octets);

        $octLength = strlen($octets);
        $septets = []; $i = 0;
        while (7 * ($i+1) < $octLength)
        {
            $septets[] = substr($octets, -7 * ($i+1), 7);
            $i++;
        }

        array_walk($septets, function (&$sept){
            $sept = bindec($sept);
        });

        return $septets;
    }

    /**
    * converts septets into octets
    *
    * @ignore
    * @param mixed $octets
    */
    private function _sept2oct($septets)
    {
        throw new \Exception('sept2oct not implemented.');


        $septCount = count($septets);
        $octets = [];

        for ($i=0; $i<$septCount; $i++)
        {
            $septLength     = strlen($septets[$i]);
            $nextSeptLength = strlen($septets[$i+1]);

            $octets[] = substr($septets[$i], $nextSeptLength - (8 - $septLength)) . $septets[$i];

            $septets[$i+1] = substr($septets[$i+1], 0, $nextSeptLength - (8 - $septLength));
            //var_dump('int septets', $septets);
        }

        die();
    }

    /**
    * decodes phone number (smsc, sender)
    *
    * @ignore
    * @param mixed $octets
    * @param mixed $length
    */
    private function _number($octets, $length, $format)
    {
        if ($format['ToN'] == 0x50)
        {
            throw new \Exception('7bit alphabet ToN not implemented');
        } else {

            $number = '';
            for ($i=0; $i<$length-1; $i++)
            {
                $number .= substr($octets[$i], 1, 1) . substr($octets[$i], 0, 1);
            }

            if ($length % 2)
            {
                $number = substr($number, 0, strlen($number) - 1);
            }

            if ($format['ToN'] = 16) return '+' . $number;
            return $number;
        }
    }

    /**
    * decodes type-of-address octet
    *
    * @ignore
    * @param mixed $octet
    */
    private function _toa_decode($octet)
    {
        $octet = intval($octet, 16);

        return [
            'ToN' => $octet & 0x70,
            'NPI' => $octet & 0xF,
            'info' => $this->ToN[$octet & 0x70] .', ' . $this->NBI[$octet & 0xF]
        ];
    }

    /**
    * encodes type-of-address octet from a phone number
    *
    * @ignore
    * @param string $phone
    */
    private function _toa_encode($phone)
    {
        $phone = preg_replace('/[^0-9\+]*/', '', $phone); //sanitize

        $octet = 0b10000000;
        $octet |= 0b00000001; // NBI = ISDN/telephone numbering plan (E.164/E.163)

        if (strpos($phone, '+') === 0)
            $octet |= 0b00010000; // international number
        else
            $octet |= 0b00000000; // unknown

        return dechex($octet);
    }

    /**
    * decodes pdutype octet
    *
    * @ignore
    * @param mixed $octet
    * @param mixed $dir
    * @return mixed
    */
    function _pdu_type($octet, $dir)
    {
        $octet = intval($octet, 16);

        $map = [
            0 => [
                0b00000000 => 'SMS-DELIVER',
                0b00000001 => 'SMS-SUBMIT',
                0b00000010 => 'SMS-STATUS-REPORT',
                0b00000011 => 'Reserved'
            ],
            1 => [
                0b00000000 => 'SMS-DELIVER-REPORT',
                0b00000001 => 'SMS-SUBMIT-REPORT',
                0b00000010 => 'SMS-COMMAND',
                0b00000011 => 'Reserved',
            ]
        ];

        $type = $octet & 0b00000011;

        if (!array_key_exists($type, $map[$dir]))
            throw new \Exception('Invalid PDU type: ' . $type);

        return [
            'mti'   => $map[$dir][$type],
            'udhi'  => ($octet & 0b1000000) > 0
        ];
        return $map[$dir][$type];
        return 'Reserved';
    }

    /**
    * decodes protocl id octet
    *
    * @ignore
    * @param mixed $octet
    */
    function _pid($octet)
    {
        $pid = intval($octet, 16);

        if ($pid)
            throw new \Exception('PDU protocol ' . $pid . ' not implemented.');

        return ['id' => $pid, 'info' => 'Default store and forward short message'];
    }

    /**
    * decodes DCS octet
    *
    * @ignore
    * @param mixed $octet
    */
    function _dcs_decode($octet)
    {
        $octet = intval($octet, 16);

        $map = [
            'dcs' => [
                0b00000000 => DCS_GSM_7bit,
                0b00000100 => DCS_8bit,
                0b00001000 => DCS_UCS2
            ],
            'class' => [
                0b00000000 => CLASS_0,
                0b00000001 => CLASS_1,
                0b00000010 => CLASS_2,
                0b00000011 => CLASS_3
            ]
        ];

        return [$map['dcs'][$octet &0b00001100], $map['class'][$octet &0b00000011]];
    }

    /**
    * encode DCS octet
    *
    * @ignore
    * @param mixed $octet
    */
    function _dcs_encode($alphabet, $class = CLASS_0)
    {
        $octet = 0x00;

        switch ($alphabet)
        {
            case DCS_GSM_7bit:
                $octet |= 0b00000000;
                break;
            case DCS_8bit:
                $octet |= 0b00000100;
                break;
            case DCS_UCS2:
                $octet |= 0b00001000;
                break;
        }

        switch ($class)
        {
            case CLASS_0:
                $octet |= 0b00000000;
                break;
            case CLASS_1:
                $octet |= 0b00000001;
                break;
            case CLASS_2:
                $octet |= 0b00000010;
                break;
            case CLASS_3:
                $octet |= 0b00000011;
                break;
        }

        return str_pad(dechex($octet), 2, '0', STR_PAD_LEFT);
    }

    /**
    * decode scts (service center time stamp)
    *
    * @ignore
    * @param mixed $octets
    * @returns string
    */
    private function _scts($octets)
    {
        for ($i=0; $i<7; $i++)
        {
            $octets[$i] = substr($octets[$i], 1, 1) . substr($octets[$i], 0, 1);
        }

        $ts = $octets[0] < 70 ? '20' : '19';

        $ts .= $octets[0] . '-' . $octets[1] . '-' . $octets[2] . ' ' . $octets[3] . ':' . $octets[4] . ':' . $octets[5] . ' GMT';

        if ($octets[6] & 0x80) {
            $octets[6] = $octets & 0x7F;
            $ts .= '-';
        }
        else {
            $ts .= '+';
        }

        $ts .= $octets[6] / 4;

        return $ts;
    }
}