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

$m = new \PhpAtz\PhpAtz([
    'device'    => '10.0.0.61:961',
    'adapter'   => 'tcp',
    'auto_init' => false,

    'sms_mode'  => 'pdu', //text

    'debug'     => true,
]);

try {
    $m->init();

    echo '<b>Connected</b>' . "\n\n";
    echo '<b>IMEI is:</b> ' . $phpatz->info->getIMEI(). "\n\n";
    echo '<b>Signal is:</b>' . $phpatz->info->getSignal(). "\n\n";
    echo '<b>Network is:</b>' . $phpatz->info->getNetwork() . "\n\n";

    echo '<b>Sending SMS message.</b> Ref: ' . $phpatz->sms->send('+40768248202', 'Some message') . "\n\n";

    echo 'Sending SMS message. Ref: ' . $m->sms->send('+40768248202', 'Some message') . "\n";

    echo 'SMS storage status: ' . "\n";
    //var_dump($m->sms->getStorageStatus());
    echo "\n";

    echo "Listing all received messages: " . "\n";
    //var_dump($m->sms->getReceived('all'));


    echo "<b>SMS all recieved:</b>" . "\n";
    $d = $phpatz->sms->getReceived('all');
    foreach ($d as $msg)
    {
        echo '[mem: ' . implode(',', $msg['mem_index']) . '] [status: ' . $msg['status'] . '] [from: ' . $msg['sender'] . '] [date: ' . $msg['date_received'] . '] ' . $msg['message'];
        echo "\n";
    }

    //var_dump($phpatz->conn->log);

}
catch (Exception $e)
{
    var_dump($m->conn->log);
    echo '[' . $e->getCode() . '] ' . $e->getMessage() . ' in ' . $e->getFile() .' on line ' . $e->getLine();
}
?>
</body>
</html>
