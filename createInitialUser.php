<?php
    require_once('config.php');
    require_once('Attorney.php');
    require_once('Message.php');

    $first = 'admin';
    $last = 'admin';
    $email = 'admin@admin.com';
    $barID = 12345;
    $password = 'admin';
    $retypePassword = 'admin';
    $header='none';
    $signature='none';
    $program=0;
    $errorMessages = new Message();
    $db = $GLOBALS['db'];

    Attorney::createNewAttorneyInDatabase($first, $last, $email, $barID, $password, $retypePassword, $header, $signature, $program, $errorMessages, $db)
    $userid = $db->insert_id;
    $db->query("UPDATE `userinfo` SET `userLevel` = 1 WHERE `userid` = " . $userid . ";");


 ?>
