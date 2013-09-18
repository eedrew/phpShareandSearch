-- phpMyAdmin SQL Dump
-- version 3.5.5
-- http://www.phpmyadmin.net
--
-- Generation Time: Sep 16, 2013 at 10:02 PM
-- Server version: 5.5.30-30.2
-- PHP Version: 5.3.17
-- 
-- SQL Table creation code for phpShare&Search V0.5
--
--LICENSE: This program is free software: you can redistribute it and/or modify
--   it under the terms of the GNU General Public License as published by
--   the Free Software Foundation, either version 3 of the License, or
--   (at your option) any later version.
--
--   This program is distributed in the hope that it will be useful,
--   but WITHOUT ANY WARRANTY; without even the implied warranty of
--   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
--   GNU General Public License for more details.
--
--   You should have received a copy of the GNU General Public License
--   along with this program (see License.txt).  If not, see <http://www.gnu.org/licenses/>.
--
-- DOCUMENTATION: Visit our SourceForge documentation page for information on installation and configuration: https://sourceforge.net/p/phpshareandsearch/wiki/Home/
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `Accounts`
--

CREATE TABLE IF NOT EXISTS `Accounts` (
  `UID` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `LastName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Type` tinyint(4) NOT NULL COMMENT 'Minister, Sunday School Teacher, etc.',
  `EmailAddress` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `RegistrationIP` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `FirstJoined` datetime NOT NULL,
  `Updated` datetime DEFAULT NULL,
  `LastAccess` datetime DEFAULT NULL,
  `Verified` tinyint(1) NOT NULL DEFAULT '0',
  `VerificationEmailAttempts` tinyint(4) NOT NULL DEFAULT '0',
  `VerificationPWAttempts` tinyint(4) NOT NULL DEFAULT '0',
  `Subscribed` tinyint(1) NOT NULL DEFAULT '0',
  `Permissions` tinyint(4) NOT NULL DEFAULT '10' COMMENT '10 - User, 50 - Moderator, 90 - Admin',
  `Blocked` tinyint(1) NOT NULL DEFAULT '0',
  `PageViews` int(10) unsigned NOT NULL DEFAULT '1',
  `Notes` tinytext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`UID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Content`
--

CREATE TABLE IF NOT EXISTS `Content` (
  `UID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Author` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ContributorIP` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `U_UID` int(11) NOT NULL,
  `Created` date NOT NULL,
  `Contributed` date NOT NULL,
  `Type` tinyint(4) NOT NULL,
  `Tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Pages` smallint(6) DEFAULT NULL,
  `FileName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FileExt` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `MIMEType` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `FileContents` longblob NOT NULL,
  `FileText` longtext COLLATE utf8_unicode_ci NOT NULL,
  `Reports` smallint(6) NOT NULL DEFAULT '0',
  `Likes` int(11) NOT NULL DEFAULT '0',
  `Downloads` int(11) NOT NULL DEFAULT '0',
  `Hidden` tinyint(1) NOT NULL DEFAULT '0',
  `Notes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `DownloadHistory`
--

CREATE TABLE IF NOT EXISTS `DownloadHistory` (
  `UID` int(11) NOT NULL AUTO_INCREMENT,
  `C_UID` int(11) NOT NULL,
  `U_UID` int(11) DEFAULT NULL COMMENT 'User UID if available',
  `IPAddress` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DateDownloaded` date NOT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `LikeReportHistory`
--

CREATE TABLE IF NOT EXISTS `LikeReportHistory` (
  `UID` int(11) NOT NULL AUTO_INCREMENT,
  `C_UID` int(11) NOT NULL,
  `U_UID` int(11) NOT NULL,
  `LikeIt` tinyint(1) DEFAULT NULL,
  `Report` tinyint(1) DEFAULT NULL,
  `Reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
