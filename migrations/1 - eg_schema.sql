-- MySQL dump 10.13  Distrib 5.7.21, for Linux (x86_64)
--
-- Host: localhost    Database: eg_db
-- ------------------------------------------------------
-- Server version	5.7.21-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `eg_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `eg_db` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `eg_db`;

--
-- Table structure for table `arrest`
--

DROP TABLE IF EXISTS `arrest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arrest` (
  `arrestID` int(11) NOT NULL AUTO_INCREMENT,
  `defendantID` int(11) NOT NULL,
  `OTN` varchar(11) DEFAULT NULL,
  `DC` int(11) DEFAULT NULL,
  `docketNumPrimary` varchar(22) NOT NULL,
  `docketNumRelated` varchar(140) DEFAULT NULL,
  `arrestingOfficer` varchar(50) DEFAULT NULL,
  `arrestDate` date DEFAULT NULL,
  `dispositionDate` date DEFAULT NULL,
  `judge` varchar(50) DEFAULT NULL,
  `costsTotal` decimal(10,2) DEFAULT NULL,
  `costsPaid` decimal(10,2) DEFAULT NULL,
  `costsCharged` decimal(10,2) DEFAULT NULL,
  `costsAdjusted` decimal(10,2) DEFAULT NULL,
  `bailTotal` decimal(10,2) DEFAULT NULL,
  `bailCharged` decimal(10,2) DEFAULT NULL,
  `bailPaid` decimal(10,2) DEFAULT NULL,
  `bailAdjusted` decimal(10,2) DEFAULT NULL,
  `bailTotalToal` decimal(10,2) DEFAULT NULL,
  `bailChargedTotal` decimal(10,2) DEFAULT NULL,
  `bailPaidTotal` decimal(10,2) DEFAULT NULL,
  `bailAdjustedTotal` decimal(10,2) DEFAULT NULL,
  `isARD` tinyint(1) NOT NULL,
  `isSummary` tinyint(1) NOT NULL,
  `county` varchar(35) NOT NULL,
  `policeLocality` varchar(50) NOT NULL,
  PRIMARY KEY (`arrestID`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charge`
--

DROP TABLE IF EXISTS `charge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charge` (
  `chargeID` int(11) NOT NULL AUTO_INCREMENT,
  `expungementID` mediumint(9) DEFAULT NULL COMMENT 'If NULL, then there is no expungement on this arrest.  Otherwise this is a many to one mapping from charges to expungement.',
  `arrestID` int(11) NOT NULL,
  `defendantID` int(11) NOT NULL,
  `chargeName` varchar(100) NOT NULL,
  `disposition` varchar(50) NOT NULL,
  `codeSection` varchar(20) NOT NULL,
  `dispDate` date NOT NULL,
  `isARD` tinyint(1) NOT NULL,
  `isExpungeableNow` tinyint(1) NOT NULL,
  `grade` enum('F1','F2','F3','M1','M2','M3','S') NOT NULL,
  `arrestDate` date NOT NULL,
  PRIMARY KEY (`chargeID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `court`
--

DROP TABLE IF EXISTS `court`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `court` (
  `courtID` int(4) NOT NULL AUTO_INCREMENT,
  `county` varchar(25) NOT NULL,
  `courtName` varchar(50) DEFAULT NULL,
  `address` varchar(50) NOT NULL,
  `address2` varchar(20) DEFAULT NULL,
  `city` varchar(25) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` int(5) NOT NULL,
  PRIMARY KEY (`courtID`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COMMENT='this table contains the name of the main court in each count';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `defendant`
--

DROP TABLE IF EXISTS `defendant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `defendant` (
  `defendantID` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(30) NOT NULL,
  `lastName` varchar(30) NOT NULL,
  `PP` int(11) DEFAULT NULL,
  `SID` varchar(12) DEFAULT NULL,
  `SSN` varchar(11) NOT NULL,
  `DOB` date NOT NULL,
  `street` varchar(70) NOT NULL,
  `street2` varchar(20) DEFAULT NULL,
  `city` varchar(25) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` mediumint(9) NOT NULL,
  `alias` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`defendantID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expungement`
--

DROP TABLE IF EXISTS `expungement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expungement` (
  `expungementID` int(11) NOT NULL AUTO_INCREMENT,
  `arrestID` int(11) NOT NULL,
  `defendantID` int(11) NOT NULL,
  `userid` smallint(6) NOT NULL COMMENT 'the attorney who performed the expungement',
  `isExpungement` tinyint(1) NOT NULL COMMENT 'true only if this is an expungement',
  `isRedaction` tinyint(1) NOT NULL COMMENT 'true if this is an expungement and if this is a redaction.',
  `isSummaryExpungement` tinyint(1) NOT NULL COMMENT 'Will be true only if this is a valid summary expungement (5 years arrest free after a guilty summary offense).',
  `numRedactableCharges` tinyint(4) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`expungementID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mdjcourt`
--

DROP TABLE IF EXISTS `mdjcourt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdjcourt` (
  `courtID` smallint(4) NOT NULL AUTO_INCREMENT,
  `district` varchar(12) NOT NULL,
  `courtName` varchar(35) NOT NULL,
  `judge` varchar(40) NOT NULL,
  `address` varchar(50) NOT NULL,
  `city` varchar(35) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(5) NOT NULL,
  `phone` varchar(13) NOT NULL,
  `fax` varchar(13) NOT NULL,
  `address2` varchar(1) NOT NULL DEFAULT '' COMMENT 'dummy field to match structure of court',
  PRIMARY KEY (`courtID`)
) ENGINE=InnoDB AUTO_INCREMENT=538 DEFAULT CHARSET=utf8 COMMENT='pulled from http://www.pacourts.us/T/SpecialCourts/MDJList.h';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `police`
--

DROP TABLE IF EXISTS `police`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `police` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `street` varchar(50) NOT NULL,
  `city` varchar(40) NOT NULL,
  `state` varchar(2) NOT NULL DEFAULT 'PA',
  `zip` varchar(5) NOT NULL,
  `phone` varchar(12) NOT NULL,
  PRIMARY KEY (`name`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `program`
--

DROP TABLE IF EXISTS `program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `program` (
  `programID` int(3) NOT NULL AUTO_INCREMENT,
  `programName` varchar(45) NOT NULL,
  `ifp` tinyint(1) NOT NULL,
  `apiKey` blob,
  `ifpLanguage` text,
  PRIMARY KEY (`programID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_calls`
--

DROP TABLE IF EXISTS `resource_calls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_calls` (
  `id` int(11) NOT NULL,
  `userid` smallint(6) NOT NULL COMMENT 'the user that made the resource call',
  `resource` varchar(100) NOT NULL COMMENT 'The endpoint called, i.e. eg-api.php',
  `action` varchar(100) DEFAULT NULL COMMENT 'The action performed on the resource, i.e. searchCPCMS or GeneratePetitions',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `userid` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'user id',
  `email` varchar(50) NOT NULL COMMENT 'email address and username',
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `email` (`email`)
<<<<<<< HEAD
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
=======
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;
>>>>>>> moved around a couple things to make dockerizing easier
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `userinfo`
--

DROP TABLE IF EXISTS `userinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userinfo` (
  `userid` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'user id',
  `firstName` varchar(15) NOT NULL COMMENT 'first name',
  `lastName` varchar(25) NOT NULL COMMENT 'last name',
  `petitionHeader` text NOT NULL COMMENT 'the attorney information to show up at the top of the expungement petition',
  `petitionSignature` text NOT NULL,
  `pabarid` int(6) NOT NULL COMMENT 'The lawyers PA Bar Identification Number',
  `programID` smallint(3) NOT NULL,
  `userLevel` tinyint(4) NOT NULL DEFAULT '0',
  `totalPetitions` mediumint(9) NOT NULL DEFAULT '0' COMMENT 'Created 9/14/2012 - tracks total petitions prepared by individual',
  `anonymous` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`)
<<<<<<< HEAD
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
=======
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;
>>>>>>> moved around a couple things to make dockerizing easier
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-02-27 16:09:45
