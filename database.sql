-- MySQL dump 10.11
--
-- Host: localhost    Database: example
-- ------------------------------------------------------
-- Server version	5.0.67-0ubuntu6

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
-- Table structure for table `Action`
--

DROP TABLE IF EXISTS `Action`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `Action` (
  `id` int(11) NOT NULL,
  `uri` varchar(1024) default NULL,
  `label` varchar(255) default NULL,
  `poiId` int(11) default NULL,
  `autoTriggerRange` int(11) default NULL,
  `autoTriggerOnly` tinyint(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `poiId` (`poiId`),
  CONSTRAINT `Action_ibfk_1` FOREIGN KEY (`poiId`) REFERENCES `POI` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Object`
--

DROP TABLE IF EXISTS `Object`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `Object` (
  `poiID` int(11) default NULL,
  `baseURL` varchar(1000) default NULL,
  `full` varchar(255) default NULL,
  `reduced` varchar(255) default NULL,
  `icon` varchar(255) default NULL,
  `size` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `POI`
--

DROP TABLE IF EXISTS `POI`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `POI` (
  `id` int(11) NOT NULL auto_increment,
  `attribution` varchar(255) default NULL,
  `imageURL` varchar(1024) default NULL,
  `lat` float default NULL,
  `lon` float default NULL,
  `line2` varchar(255) default NULL,
  `line3` varchar(255) default NULL,
  `line4` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `alt` int(11) default NULL,
  `relativeAlt` int(11) default NULL,
  `dimension` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Transform`
--

DROP TABLE IF EXISTS `Transform`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `Transform` (
  `poiID` int(11) default NULL,
  `rel` tinyint(1) default NULL,
  `angle` int(11) default NULL,
  `scale` float default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-12-14 17:21:00
