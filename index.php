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

try {
    $phpatz = new \PhpAtz\PhpAtz([
        'device'    => '10.0.0.61:961',
        'adapter'   => 'tcp',

        'sms_mode'  => 'pdu', //text

        'debug'     => true,
    ]);

    echo '<b>Connected</b>' . "\n\n";
    echo '<b>IMEI is:</b> ' . $phpatz->info->getIMEI(). "\n\n";
    echo '<b>Signal is:</b>' . $phpatz->info->getSignal(). "\n\n";
    echo '<b>Network is:</b>' . $phpatz->info->getNetwork() . "\n\n";

    echo '<b>Sending SMS message.</b> Ref: ' . $phpatz->sms->send('+40768248202', 'Some message') . "\n\n";

    echo '<b>SMS storage status:</b> ';
    $d = $phpatz->sms->getStorageStatus();
    printf(
        'read(%s %d/%d) write(%s %d/%d) store(%s %d/%d)',
        $d['read']['type'], $d['read']['used'], $d['read']['total'],
        $d['write']['type'], $d['write']['used'], $d['write']['total'],
        $d['store']['type'], $d['store']['used'], $d['store']['total']
    );
    echo "\n\n";

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
    echo '[' . $e->getCode() . '] ' . $e->getMessage() . ' in ' . $e->getFile() .' on line ' . $e->getLine();
}
?>
</body>
</html>
