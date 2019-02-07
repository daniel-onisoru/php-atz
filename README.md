# php-atz
This is very much a work in progress but under active development.  
Pull requests are welcome.


# Installing
Use [composer](https://getcomposer.org/) to install php-atz into your project:  
```
composer require daniel-onisoru/php-atz
```

In your php file:

```php
require 'vendor/autoload.php';

$dev = new PhpAtz\PhpAtz([
  'device' => '/dev/ttyS0'
]);
```

# Examples
### Sending SMS message
```php
$ref = $dev->sms->send('+0000000000', 'Text message here');
```
### Reading all received SMS Messages
```php
$messages = $dev->sms->getReceived('all');
```

# Configuration
| Options   | Allowed values  | Default     | Description |
| ---       | ---             | ---         | --- |
| device    | *string*        | *empty*      | Device name or address. Ex: /dev/ttyS0, 127.0.0.1:961 |
| adapter   | Serial, Tcp     | Serial | Connection addapter. Tcp is used for serail over tcp connections. |
| debug     | *true*, *false* | *false* | Debug mode enables logging of serial communication. |
| **SMS Module**      |                 |             |             |
| sms_mode  | pdu, text       | pdu | Mode for sending and recieving sms messages. |

# Function reference
| Function  | Params  | Description |
| ---       | ---     | ---         |
| **Info**  |         |             |
| getIMEI   | *none*  | Device IMEI or false on failure. |
| getSignal | *none*  | Float value with network signal. On failure it returns false or 99.9 |
| getNetwork | *none* | Network name and country. False on failure. |
| **SMS**  |         |             |
| send | $phone, $message | Sends $message to $phone. Returns message ref number or false on failure. |
