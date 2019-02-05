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

    echo 'Connected' . "\n";
    echo 'IMEI is: ' . $m->info->getIMEI(). "\n";
    echo 'Signal is: ' . $m->info->getSignal(). "\n";
    echo 'Network is: ' . $m->info->getNetwork() . "\n";


    echo 'Sending SMS message. Ref: ' . $m->sms->send('+40768248202', 'Some message') . "\n";

    echo 'SMS storage status: ' . "\n";
    //var_dump($m->sms->getStorageStatus());
    echo "\n";

    echo "Listing all received messages: " . "\n";
    //var_dump($m->sms->getReceived('all'));



    var_dump($m->conn->log);

}
catch (Exception $e)
{
    var_dump($m->conn->log);
    echo '[' . $e->getCode() . '] ' . $e->getMessage() . ' in ' . $e->getFile() .' on line ' . $e->getLine();
}





/*

  string(61) "R: +CMGL: 3,"REC READ","+40768248202",,"19/01/29,20:58:21+08""
  [38]=>
  string(7) "R: Rryy"
  [39]=>
  string(61) "R: +CMGL: 4,"REC READ","+40768248202",,"19/01/29,21:44:47+08""
  [40]=>
  string(155) "R: Hdhsjsbs us sus sus su sus sus va si dvs eu dvs va zis aia zis jzbsjs zis eu aia aia va av9h g ochi kg in oh fi  in in h k in oh kg kg h  k    kg k kg g"
  [41]=>
  string(61) "R: +CMGL: 5,"REC READ","+40768248202",,"19/01/29,21:44:48+08""
  [42]=>
  string(20) "R: k k g ggggh nu jf"
  [43]=>
  string(61) "R: +CMGL: 6,"REC READ","+40768248202",,"19/01/30,03:54:35+08""
  [44]=>
  string(156) "R: HHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHJHHHHHHHHHHHHHHHHHH8XIGXGIXIGCGIXGIXIGXGIXIGXIGXIGXIGXGIXIGXIGXTIXITXXITXGIXGIXIGXIGXGIXIGXIGXGIXIGXIGXITXIGXGIXI"
  [45]=>
  string(61) "R: +CMGL: 7,"REC READ","+40768248202",,"19/01/30,03:54:36+08""
  [46]=>
  string(58) "R: GXIGXIGXIGXIGXIGXIGXIGXIGXIGXIGXIGXIGXIGXIGXIGXIGXIGXGI"
  [47]=>
  string(61) "R: +CMGL: 8,"REC READ","+40768248202",,"19/01/30,04:01:13+08""
  [48]=>
  string(43) "R: Ana are mere. Ana are mere.Ana are mere."
  [49]=>
  string(3) "R: "
  [50]=>
  string(5) "R: OK"
                         */




/*var_dump(
        $m->pdu->decode('07910467060095F0440B910467288402F2000091100330455380A0050003C30201904824128944229148241289442291482412894422914824128944229148241289442295482412894422914824128944229148240E9B3C628F496CF2383C26B1C7243679C41E93D8E4119B3C6293476CF2883D26B1C9233679C45293D824158B4D52B1C724F698C4268FD8E4117B4C6293476CF2883D26B1C9233679C426A9D8E4117B4C6293')
    );

    var_dump(
        $m->pdu->decode('07910467060095F0240B910467288402F20000911003401031802941771814969741EDB2BCEC0205DD6150585E06B5CBF2B22BE80E83C2F232A85D96975D20')
    );*/
    //var_dump(
        //$m->pdu->decode('07910467060095F0440B910467288402F2000091100330455380A0050003C30201904824128944229148241289442291482412894422914824128944229148241289442295482412894422914824128944229148240E9B3C628F496CF2383C26B1C7243679C41E93D8E4119B3C6293476CF2883D26B1C9233679C45293D824158B4D52B1C724F698C4268FD8E4117B4C6293476CF2883D26B1C9233679C426A9D8E4117B4C6293')
        //$m->pdu->decode('07910467060095F0240B910467288402F200009110920294238003C8391A') //hsh
        //$m->pdu->decode('07910467060085F0240B910467288402F200009110920235508004C7F3F90C') //Gggg
        //$m->pdu->decode('07910467060085F0240B910467288402F20000911092028512800452793E0F') //Rryy
        //$m->pdu->decode('07910467060095F0440B910467288402F2000091109212447480A0050003C202019064F45C3D17CF41F539685E9F83E6F539685E07CDEB73D0BC3E07D9C3A0791A44B6CF41E53A886C9F83EC61903E3D0785D361903E3D07A9F5E2B97A0ED2A7E7A0721D144E8741E17418640F83C2F61C1A7406BDC7E834687D06A5DDA0371A644E8340693728ED06A1416B50DA0D7AA341EB33687D06A141A035080402ADCFA035687D069D41') //
        //$m->pdu->decode('07910467060095F0640B910467288402F200009110921244848018050003C20202D6A035E80C3A9FCF6734C85D07A9CD') //
        //$m->pdu->decode('07910467060095F0240B910467288402F20000911003401031802941771814969741EDB2BCEC0205DD6150585E06B5CBF2B22BE80E83C2F232A85D96975D20') //
    //);

?>
</body>
</html>
