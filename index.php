<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>PhpAtz Demo</title>
</head>
<body>
<pre>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
set_time_limit(10);


require_once 'vendor/autoload.php';

$dev = new \PhpAtz\PhpAtz([
    'device'    => '10.0.0.61:961',
    'adapter'   => 'tcp',
    'auto_init' => false,

    'sms_mode'  => 'pdu', //text

    'debug'     => true,
]);

try {
    $dev->init();

    echo '<b>Connected</b>' . "\n\n";
    echo '<b>IMEI is:</b> ' . $dev->info->getIMEI(). "\n\n";
    echo '<b>Signal is:</b>' . $dev->info->getSignal(). "\n\n";
    echo '<b>Network is:</b>' . $dev->info->getNetwork() . "\n\n";

    /*
    AT+CMGS=57
    00 11 00 0A 81 70864228200008FF2C004D006500730061006A002000EE006E0020006C0069006D0062006100200072006F006D00E2006E0103002E
    00 11 00 0A a1 70864228200008FF2c004d006500730061006a002000ee006e0020006c0069006d0062006100200072006f006d00e2006e0103002e
    */

    echo '<b>Sending SMS message.</b> Ref: ' . $dev->sms->send('0768248202', 'Mesaj în limba română.') . "\n\n";

    /*
    echo 'SMS storage status: ' . "\n";
    var_dump($m->sms->getStorageStatus());
    echo "\n";
    */

    /*
    echo "<b>SMS all recieved:</b>" . "\n";
    $d = $dev->sms->getReceived('all');
    foreach ($d as $msg)
    {
        echo '[mem: ' . implode(',', $msg['mem_index']) . '] [status: ' . $msg['status'] . '] [from: ' . $msg['sender'] . '] [date: ' . $msg['date_received'] . '] ' . $msg['message'];
        echo "\n";
    }*/

    var_dump($dev->conn->log);

}
catch (Exception $e)
{
    var_dump($dev->conn->log);
    echo '[' . $e->getCode() . '] ' . $e->getMessage() . ' in ' . $e->getFile() .' on line ' . $e->getLine();
}
?>
</body>
</html>
