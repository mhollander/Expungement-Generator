#!/bin/bash

php createInitialUser.php
rm createInitialUser.php


#
# USE `eg_db`;
#
# INSERT INTO `user` (`email`,`password`) VALUES ('admin@admin.com','$2y$10$FWYGQBRKvv3Cu1z2LEfrbu/lUDGEKvuwYyqdh9IpJDwSgwdDYIk/6r');
#
# INSERT INTO `userinfo` (`firstName`,`userlevel`,`lastName`,`petitionHeader`,`petitionSignature`,`pabarid`,`programID`) VALUES     ('admin',1,'admin','n/a','n/a',0,0);
