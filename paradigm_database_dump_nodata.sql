-- MySQL dump 10.13  Distrib 5.5.44, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: paradigm
-- ------------------------------------------------------
-- Server version	5.5.44-0ubuntu0.14.04.1

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
-- Table structure for table `candidate`
--

DROP TABLE IF EXISTS `candidate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate` (
  `id_candidate` int(11) NOT NULL AUTO_INCREMENT,
  `id_surface` int(11) DEFAULT NULL,
  `id_lemma` int(11) NOT NULL,
  `id_paradigm` int(11) NOT NULL,
  `id_expanded` int(11) NOT NULL,
  `probability` decimal(7,6) NOT NULL,
  `id_pos` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_candidate`)
) ENGINE=InnoDB AUTO_INCREMENT=2993048 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expanded`
--

DROP TABLE IF EXISTS `expanded`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expanded` (
  `id_expanded` int(11) NOT NULL AUTO_INCREMENT,
  `value_expanded` text COLLATE utf8_bin,
  PRIMARY KEY (`id_expanded`)
) ENGINE=InnoDB AUTO_INCREMENT=2993048 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `flag`
--

DROP TABLE IF EXISTS `flag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flag` (
  `id_flag` int(11) NOT NULL AUTO_INCREMENT,
  `value_flag` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `id_pos` int(11) DEFAULT NULL,
  `id_lang` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_flag`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lang`
--

DROP TABLE IF EXISTS `lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lang` (
  `id_lang` int(11) NOT NULL AUTO_INCREMENT,
  `shortname_lang` varchar(4) COLLATE utf8_bin DEFAULT NULL,
  `longname_lang` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id_lang`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lemma`
--

DROP TABLE IF EXISTS `lemma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lemma` (
  `id_lemma` int(11) NOT NULL AUTO_INCREMENT,
  `value_lemma` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id_lemma`)
) ENGINE=InnoDB AUTO_INCREMENT=1413138 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paradigm`
--

DROP TABLE IF EXISTS `paradigm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paradigm` (
  `id_paradigm` int(11) NOT NULL AUTO_INCREMENT,
  `value_paradigm` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `id_pos` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_paradigm`)
) ENGINE=InnoDB AUTO_INCREMENT=1263084 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos`
--

DROP TABLE IF EXISTS `pos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pos` (
  `id_pos` int(11) NOT NULL AUTO_INCREMENT,
  `label_pos` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `short_pos` varchar(2) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id_pos`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `speak`
--

DROP TABLE IF EXISTS `speak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `speak` (
  `id_speak` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  PRIMARY KEY (`id_speak`)
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `specific_type`
--

DROP TABLE IF EXISTS `specific_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `specific_type` (
  `id_specific_type` int(10) NOT NULL AUTO_INCREMENT,
  `value_specific_type` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `id_pos` int(11) DEFAULT NULL,
  `id_lang` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_specific_type`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surface`
--

DROP TABLE IF EXISTS `surface`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surface` (
  `id_surface` int(11) NOT NULL AUTO_INCREMENT,
  `value_surface` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `top_pos_id` int(11) DEFAULT NULL,
  `xval_surface` int(1) DEFAULT NULL,
  `lang_surface` int(11) DEFAULT NULL,
  `id_task` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_surface`)
) ENGINE=InnoDB AUTO_INCREMENT=35271 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task` (
  `id_task` int(11) NOT NULL AUTO_INCREMENT,
  `id_lang` int(11) DEFAULT NULL,
  `date_create` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activate_task` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_task`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_config`
--

DROP TABLE IF EXISTS `task_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_config` (
  `tasks_cluster_size` int(11) DEFAULT NULL,
  `priority` varchar(10) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `name_user` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `pwd_user` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `email_user` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `activate_user` tinyint(1) NOT NULL,
  `user_online` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_surface_done`
--

DROP TABLE IF EXISTS `user_surface_done`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_surface_done` (
  `id_user` int(11) DEFAULT NULL,
  `id_surface` int(11) DEFAULT NULL,
  `id_candidate` int(11) DEFAULT NULL,
  `date_done` varchar(14) COLLATE utf8_bin DEFAULT NULL,
  `id_pos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_surface_expanded_lock`
--

DROP TABLE IF EXISTS `user_surface_expanded_lock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_surface_expanded_lock` (
  `id_user` int(11) DEFAULT NULL,
  `id_surface` int(11) DEFAULT NULL,
  `value_surface` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `date_expanded_lock` varchar(14) COLLATE utf8_bin DEFAULT NULL,
  `id_pos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_surface_flag`
--

DROP TABLE IF EXISTS `user_surface_flag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_surface_flag` (
  `id_user` int(11) DEFAULT NULL,
  `id_surface` int(11) DEFAULT NULL,
  `id_flag` int(11) DEFAULT NULL,
  `date_flag` varchar(14) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_surface_lock`
--

DROP TABLE IF EXISTS `user_surface_lock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_surface_lock` (
  `id_user` int(11) DEFAULT NULL,
  `id_surface` int(11) DEFAULT NULL,
  `date_lock` varchar(14) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_surface_specific`
--

DROP TABLE IF EXISTS `user_surface_specific`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_surface_specific` (
  `id_user` int(11) DEFAULT NULL,
  `id_surface` int(11) DEFAULT NULL,
  `id_specific_type` int(11) DEFAULT NULL,
  `date_specific` varchar(14) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_task`
--

DROP TABLE IF EXISTS `user_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_task` (
  `id_user` int(11) DEFAULT NULL,
  `id_task` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-11-30 19:30:46
