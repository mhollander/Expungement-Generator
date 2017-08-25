<?php

/********************************************
* pwupdate.php
* 
* Goes through the database and updates each password to be an bcrypt of the password.
* This is used to convert from an MD5 hash, which was originally used in the EG
* to a bcrypt hash, which is apparently ever more secure.  
* 
* In order to update the EG without affecting users and to do so immediately
* this script immediately updates all passwords to bcrypt(md5(password))
* and then all future checks on a password entered will run the same hash.
*******************************************/


require("dbconnect.php");

foreach($db->query("SELECT userid, password FROM user") as $users) 
{
    # don't do this if we already have converted the PWs!
    if (strlen($users['password']) == 32)
    {
        #otherwise run bcrypt on the password and update the database
        $bcrypt_hash = password_hash($users['password'], PASSWORD_BCRYPT);
        if($bcrypt_hash) 
        {
            $query = "UPDATE user SET password='{$bcrypt_hash}' WHERE userid='{$users['userid']}';";
            $db->query($query);
        }
    }
}