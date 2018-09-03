-- MySQL dump 10.13  Distrib 5.7.9, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: test
-- ------------------------------------------------------
-- Server version	5.5.5-10.1.21-MariaDB

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
-- Table structure for table `forum_post`
--

DROP TABLE IF EXISTS `forum_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_post` (
  `idForumPost` int(11) NOT NULL AUTO_INCREMENT,
  `content` mediumtext,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `idForumUser` int(11) NOT NULL,
  `idForumTopic` int(11) NOT NULL,
  PRIMARY KEY (`idForumPost`),
  KEY `fk_forum_post_forum_user1_idx` (`idForumUser`),
  KEY `fk_forum_post_forum_topic1_idx` (`idForumTopic`),
  CONSTRAINT `fk_forum_post_forum_topic1` FOREIGN KEY (`idForumTopic`) REFERENCES `forum_topic` (`idForumTopic`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_forum_post_forum_user1` FOREIGN KEY (`idForumUser`) REFERENCES `forum_user` (`idForumUser`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_post`
--

LOCK TABLES `forum_post` WRITE;
/*!40000 ALTER TABLE `forum_post` DISABLE KEYS */;
INSERT INTO `forum_post` VALUES (1,'Regulamin','2018-09-03 12:56:44',1,1),(2,'Regulamin','2018-09-03 12:56:44',1,2),(3,'Regulamin','2018-09-03 12:56:44',1,3),(4,'Regulamin','2018-09-03 12:56:44',1,4),(5,'Regulamin','2018-09-03 12:56:44',1,5),(6,'Regulamin','2018-09-03 12:56:44',1,6),(7,'Regulamin','2018-09-03 12:56:44',1,7);
/*!40000 ALTER TABLE `forum_post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_section`
--

DROP TABLE IF EXISTS `forum_section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_section` (
  `idForumSection` int(11) NOT NULL AUTO_INCREMENT,
  `nameSection` varchar(128) DEFAULT NULL,
  `idSubforum` int(11) NOT NULL,
  PRIMARY KEY (`idForumSection`),
  KEY `fk_forum_section_forum_subforum1_idx` (`idSubforum`),
  CONSTRAINT `fk_forum_section_forum_subforum1` FOREIGN KEY (`idSubforum`) REFERENCES `forum_subforum` (`idSubforum`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_section`
--

LOCK TABLES `forum_section` WRITE;
/*!40000 ALTER TABLE `forum_section` DISABLE KEYS */;
INSERT INTO `forum_section` VALUES (1,'Regulamin',1),(2,'Nowości',1),(3,'Przedstaw się',2),(4,'Opinie i krytyka',2),(5,'Propozycje',2),(6,'Offtopic',3),(7,'O wszystkim i o niczym',3);
/*!40000 ALTER TABLE `forum_section` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_subforum`
--

DROP TABLE IF EXISTS `forum_subforum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_subforum` (
  `idSubforum` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`idSubforum`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_subforum`
--

LOCK TABLES `forum_subforum` WRITE;
/*!40000 ALTER TABLE `forum_subforum` DISABLE KEYS */;
INSERT INTO `forum_subforum` VALUES (1,'Forum główne'),(2,'Dyskusja'),(3,'Offtopic');
/*!40000 ALTER TABLE `forum_subforum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_topic`
--

DROP TABLE IF EXISTS `forum_topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_topic` (
  `idForumTopic` int(11) NOT NULL AUTO_INCREMENT,
  `nameTopic` varchar(128) DEFAULT NULL,
  `idForumUser` int(11) NOT NULL,
  `idForumSection` int(11) NOT NULL,
  `open` int(11) DEFAULT '1',
  PRIMARY KEY (`idForumTopic`),
  KEY `fk_forum_topic_forum_user1_idx` (`idForumUser`),
  KEY `fk_forum_topic_forum_section1_idx` (`idForumSection`),
  CONSTRAINT `fk_forum_topic_forum_section1` FOREIGN KEY (`idForumSection`) REFERENCES `forum_section` (`idForumSection`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_forum_topic_forum_user1` FOREIGN KEY (`idForumUser`) REFERENCES `forum_user` (`idForumUser`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_topic`
--

LOCK TABLES `forum_topic` WRITE;
/*!40000 ALTER TABLE `forum_topic` DISABLE KEYS */;
INSERT INTO `forum_topic` VALUES (1,'Regulamin',1,1,0),(2,'Regulamin',1,2,0),(3,'Regulamin',1,3,0),(4,'Regulamin',1,4,0),(5,'Regulamin',1,5,0),(6,'Regulamin',1,6,0),(7,'Regulamin',1,7,0);
/*!40000 ALTER TABLE `forum_topic` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_user`
--

DROP TABLE IF EXISTS `forum_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_user` (
  `idForumUser` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `idForumUserRole` int(11) NOT NULL,
  PRIMARY KEY (`idForumUser`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `fk_forum_user_forum_userrole1_idx` (`idForumUserRole`),
  CONSTRAINT `fk_forum_user_forum_userrole1` FOREIGN KEY (`idForumUserRole`) REFERENCES `forum_userrole` (`idForumUserRole`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_user`
--

LOCK TABLES `forum_user` WRITE;
/*!40000 ALTER TABLE `forum_user` DISABLE KEYS */;
INSERT INTO `forum_user` VALUES (1,'Administrator','$2y$13$bKrp525bm5zUeVZwhsxtvu2jpeEVrpwoQKpNvJcI5lObl3nnQaeQa',1),(2,'User','$2y$13$53zy7wfXWt9WApa1n1.ba.ZKXaShgKA2jlGLOW9Vi2mTx20mEdNG.',1);
/*!40000 ALTER TABLE `forum_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_user_data`
--

DROP TABLE IF EXISTS `forum_user_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_user_data` (
  `idForumUserData` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `surname` varchar(45) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `idForumUser` int(11) NOT NULL,
  PRIMARY KEY (`idForumUserData`,`idForumUser`),
  KEY `fk_forum_user_data_forum_user_idx` (`idForumUser`),
  CONSTRAINT `fk_forum_user_data_forum_user` FOREIGN KEY (`idForumUser`) REFERENCES `forum_user` (`idForumUser`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_user_data`
--

LOCK TABLES `forum_user_data` WRITE;
/*!40000 ALTER TABLE `forum_user_data` DISABLE KEYS */;
INSERT INTO `forum_user_data` VALUES (1,'Adam','Nowak','adnow@gmail.com','1996-02-22',1),(2,'Piotr','Kowalski','pioko@gmail.com','1992-01-01',2);
/*!40000 ALTER TABLE `forum_user_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_userrole`
--

DROP TABLE IF EXISTS `forum_userrole`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_userrole` (
  `idForumUserRole` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`idForumUserRole`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_userrole`
--

LOCK TABLES `forum_userrole` WRITE;
/*!40000 ALTER TABLE `forum_userrole` DISABLE KEYS */;
INSERT INTO `forum_userrole` VALUES (0,'ROLE_ADMIN'),(1,'ROLE_USER');
/*!40000 ALTER TABLE `forum_userrole` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-09-03 12:58:19
