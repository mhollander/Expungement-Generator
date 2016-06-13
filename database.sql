-- phpMyAdmin SQL Dump
-- version 4.4.13.1
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Jun 13, 2016 at 09:10 AM
-- Server version: 5.6.27
-- PHP Version: 5.5.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eg_test_site`
--

-- --------------------------------------------------------

--
-- Table structure for table `arrest`
--

CREATE TABLE IF NOT EXISTS `arrest` (
  `arrestID` int(11) NOT NULL,
  `defendantID` int(11) NOT NULL,
  `OTN` varchar(11) DEFAULT NULL,
  `DC` int(11) DEFAULT NULL,
  `docketNumPrimary` varchar(22) NOT NULL,
  `docketNumRelated` varchar(140) DEFAULT NULL,
  `arrestingOfficer` varchar(50) DEFAULT NULL,
  `arrestDate` date NOT NULL,
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
  `policeLocality` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `charge`
--

CREATE TABLE IF NOT EXISTS `charge` (
  `chargeID` int(11) NOT NULL,
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
  `arrestDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `court`
--

CREATE TABLE IF NOT EXISTS `court` (
  `courtID` int(4) NOT NULL,
  `county` varchar(25) NOT NULL,
  `courtName` varchar(50) DEFAULT NULL,
  `address` varchar(50) NOT NULL,
  `address2` varchar(20) DEFAULT NULL,
  `city` varchar(25) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='this table contains the name of the main court in each count';

-- --------------------------------------------------------

--
-- Table structure for table `defendant`
--

CREATE TABLE IF NOT EXISTS `defendant` (
  `defendantID` int(11) NOT NULL,
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
  `alias` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `expungement`
--

CREATE TABLE IF NOT EXISTS `expungement` (
  `expungementID` int(11) NOT NULL,
  `arrestID` int(11) NOT NULL,
  `defendantID` int(11) NOT NULL,
  `userid` smallint(6) NOT NULL COMMENT 'the attorney who performed the expungement',
  `isExpungement` tinyint(1) NOT NULL COMMENT 'true only if this is an expungement',
  `isRedaction` tinyint(1) NOT NULL COMMENT 'true if this is an expungement and if this is a redaction.',
  `isSummaryExpungement` tinyint(1) NOT NULL COMMENT 'Will be true only if this is a valid summary expungement (5 years arrest free after a guilty summary offense).',
  `numRedactableCharges` tinyint(4) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mdjcourt`
--

CREATE TABLE IF NOT EXISTS `mdjcourt` (
  `courtID` smallint(4) NOT NULL,
  `district` varchar(12) NOT NULL,
  `courtName` varchar(35) NOT NULL,
  `judge` varchar(40) NOT NULL,
  `address` varchar(50) NOT NULL,
  `city` varchar(35) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(5) NOT NULL,
  `phone` varchar(13) NOT NULL,
  `fax` varchar(13) NOT NULL,
  `address2` varchar(1) NOT NULL DEFAULT '' COMMENT 'dummy field to match structure of court'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='pulled from http://www.pacourts.us/T/SpecialCourts/MDJList.h';

-- --------------------------------------------------------

--
-- Table structure for table `police`
--

CREATE TABLE IF NOT EXISTS `police` (
  `name` varchar(40) NOT NULL DEFAULT '',
  `street` varchar(50) NOT NULL,
  `city` varchar(40) NOT NULL,
  `state` varchar(2) NOT NULL DEFAULT 'PA',
  `zip` varchar(5) NOT NULL,
  `phone` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `program`
--

CREATE TABLE IF NOT EXISTS `program` (
  `programID` int(3) NOT NULL,
  `programName` varchar(45) NOT NULL,
  `ifp` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `userid` smallint(6) NOT NULL COMMENT 'user id',
  `email` varchar(50) NOT NULL COMMENT 'email address and username',
  `password` varchar(32) NOT NULL COMMENT 'password, encrypted with md5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `userinfo`
--

CREATE TABLE IF NOT EXISTS `userinfo` (
  `userid` smallint(6) NOT NULL COMMENT 'user id',
  `firstName` varchar(15) NOT NULL COMMENT 'first name',
  `lastName` varchar(25) NOT NULL COMMENT 'last name',
  `petitionHeader` text NOT NULL COMMENT 'the attorney information to show up at the top of the expungement petition',
  `petitionSignature` text NOT NULL,
  `pabarid` int(6) NOT NULL COMMENT 'The lawyers PA Bar Identification Number',
  `programID` smallint(3) NOT NULL,
  `userLevel` tinyint(4) NOT NULL DEFAULT '0',
  `totalPetitions` mediumint(9) NOT NULL COMMENT 'Created 9/14/2012 - tracks total petitions prepared by individual',
  `anonymous` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `arrest`
--
ALTER TABLE `arrest`
  ADD PRIMARY KEY (`arrestID`);

--
-- Indexes for table `charge`
--
ALTER TABLE `charge`
  ADD PRIMARY KEY (`chargeID`);

--
-- Indexes for table `court`
--
ALTER TABLE `court`
  ADD PRIMARY KEY (`courtID`);

--
-- Indexes for table `defendant`
--
ALTER TABLE `defendant`
  ADD PRIMARY KEY (`defendantID`);

--
-- Indexes for table `expungement`
--
ALTER TABLE `expungement`
  ADD PRIMARY KEY (`expungementID`);

--
-- Indexes for table `mdjcourt`
--
ALTER TABLE `mdjcourt`
  ADD PRIMARY KEY (`courtID`);

--
-- Indexes for table `police`
--
ALTER TABLE `police`
  ADD PRIMARY KEY (`name`),
  ADD FULLTEXT KEY `name` (`name`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`programID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `userinfo`
--
ALTER TABLE `userinfo`
  ADD PRIMARY KEY (`userid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `arrest`
--
ALTER TABLE `arrest`
  MODIFY `arrestID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `charge`
--
ALTER TABLE `charge`
  MODIFY `chargeID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `court`
--
ALTER TABLE `court`
  MODIFY `courtID` int(4) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `defendant`
--
ALTER TABLE `defendant`
  MODIFY `defendantID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `expungement`
--
ALTER TABLE `expungement`
  MODIFY `expungementID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mdjcourt`
--
ALTER TABLE `mdjcourt`
  MODIFY `courtID` smallint(4) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `programID` int(3) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userid` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'user id';
--
-- AUTO_INCREMENT for table `userinfo`
--
ALTER TABLE `userinfo`
  MODIFY `userid` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'user id';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
