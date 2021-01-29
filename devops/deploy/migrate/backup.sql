-- MySQL dump 10.13  Distrib 8.0.22, for Linux (x86_64)
--
-- Host: localhost    Database: one_user_info
-- ------------------------------------------------------
-- Server version	8.0.22-0ubuntu0.20.04.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mod_users`
--

DROP TABLE IF EXISTS `mod_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mod_users` (
  `id` varchar(13) NOT NULL,
  `name` text,
  `description` text,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `phone` bigint unsigned NOT NULL,
  `password` text NOT NULL,
  `avatar` varchar(13) DEFAULT NULL,
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `dkey` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mod_users`
--

LOCK TABLES `mod_users` WRITE;
/*!40000 ALTER TABLE `mod_users` DISABLE KEYS */;
INSERT INTO `mod_users` VALUES ('5f9393094831e','Shade Efiong-Bassey',NULL,'folarinjmartins@gmail.com',2348118000808,'46491ea7c6279f89b6794dcc149ac71c','5fab250a68842',0,NULL,'2020-10-24 02:35:53','2021-01-21 05:10:22'),('5f93b0af61df1','FolarinNG!',NULL,'folarin@engineer.com',2348066288220,'46491ea7c6279f89b6794dcc149ac71c','5fa1513c59d91',0,NULL,'2020-10-24 04:42:23','2021-01-21 05:10:22'),('5fb3e4a6d0d4a','Root','Root','folarinjmartins@yahoo.com',2348000000000,'1d8cf5e2-e89e5f7d-4db85e05-8531212e-1460d167',NULL,0,NULL,'2020-11-17 14:56:04','2021-01-21 05:10:22');
/*!40000 ALTER TABLE `mod_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-01-28  9:37:24
