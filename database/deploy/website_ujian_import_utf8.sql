-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: website_ujian
-- ------------------------------------------------------
-- Server version	8.0.30

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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_answers`
--

DROP TABLE IF EXISTS `exam_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exam_attempt_id` bigint unsigned NOT NULL,
  `exam_question_id` bigint unsigned NOT NULL,
  `exam_option_id` bigint unsigned DEFAULT NULL,
  `answer_text` text COLLATE utf8mb4_unicode_ci,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_answers_exam_attempt_id_exam_question_id_unique` (`exam_attempt_id`,`exam_question_id`),
  KEY `exam_answers_exam_question_id_foreign` (`exam_question_id`),
  KEY `exam_answers_exam_option_id_foreign` (`exam_option_id`),
  CONSTRAINT `exam_answers_exam_attempt_id_foreign` FOREIGN KEY (`exam_attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_answers_exam_option_id_foreign` FOREIGN KEY (`exam_option_id`) REFERENCES `exam_options` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exam_answers_exam_question_id_foreign` FOREIGN KEY (`exam_question_id`) REFERENCES `exam_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_answers`
--

LOCK TABLES `exam_answers` WRITE;
/*!40000 ALTER TABLE `exam_answers` DISABLE KEYS */;
INSERT INTO `exam_answers` VALUES (1,2,5,19,NULL,'2026-02-15 16:36:54','2026-02-15 16:36:50','2026-02-15 16:36:54'),(2,2,6,23,NULL,'2026-02-15 16:36:54','2026-02-15 16:36:53','2026-02-15 16:36:54'),(3,3,7,27,NULL,'2026-02-18 09:09:26','2026-02-18 04:32:17','2026-02-18 09:09:26'),(4,3,8,32,NULL,'2026-02-18 09:09:26','2026-02-18 04:32:25','2026-02-18 09:09:26'),(5,4,17,79,NULL,'2026-02-18 08:01:37','2026-02-18 08:01:17','2026-02-18 08:01:37'),(6,4,18,83,NULL,'2026-02-18 08:01:37','2026-02-18 08:01:23','2026-02-18 08:01:37'),(7,4,19,87,NULL,'2026-02-18 08:01:37','2026-02-18 08:01:27','2026-02-18 08:01:37'),(8,4,20,91,NULL,'2026-02-18 08:01:37','2026-02-18 08:01:31','2026-02-18 08:01:37'),(9,4,21,95,NULL,'2026-02-18 08:01:37','2026-02-18 08:01:34','2026-02-18 08:01:37');
/*!40000 ALTER TABLE `exam_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_attempt_audits`
--

DROP TABLE IF EXISTS `exam_attempt_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_attempt_audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exam_attempt_id` bigint unsigned NOT NULL,
  `actor_user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exam_attempt_audits_exam_attempt_id_foreign` (`exam_attempt_id`),
  KEY `exam_attempt_audits_actor_user_id_foreign` (`actor_user_id`),
  CONSTRAINT `exam_attempt_audits_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exam_attempt_audits_exam_attempt_id_foreign` FOREIGN KEY (`exam_attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_attempt_audits`
--

LOCK TABLES `exam_attempt_audits` WRITE;
/*!40000 ALTER TABLE `exam_attempt_audits` DISABLE KEYS */;
INSERT INTO `exam_attempt_audits` VALUES (1,3,2,'reopen_attempt','Ada kendala teknis',NULL,NULL,NULL,'2026-02-18 04:33:22','2026-02-18 04:33:22'),(2,3,2,'force_submit','Jaringan error',NULL,NULL,NULL,'2026-02-18 04:33:54','2026-02-18 04:33:54'),(3,3,2,'reopen_attempt','Ada kendala teknis',NULL,NULL,NULL,'2026-02-18 05:08:38','2026-02-18 05:08:38'),(4,3,2,'reopen_attempt','<script>alert(1)</script>',NULL,NULL,NULL,'2026-02-18 05:15:49','2026-02-18 05:15:49'),(5,3,2,'force_submit','<script>alert(1)</script>',NULL,NULL,NULL,'2026-02-18 05:16:03','2026-02-18 05:16:03'),(6,3,2,'reopen_attempt','orang dalemnya gua','192.168.0.162','Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/14.2 Chrome/87.0.4280.141 Mobile Safari/537.36',NULL,'2026-02-18 09:09:10','2026-02-18 09:09:10'),(7,3,2,'force_submit','orang dalemnya gua','192.168.0.162','Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/14.2 Chrome/87.0.4280.141 Mobile Safari/537.36',NULL,'2026-02-18 09:09:26','2026-02-18 09:09:26');
/*!40000 ALTER TABLE `exam_attempt_audits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_attempts`
--

DROP TABLE IF EXISTS `exam_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('active','submitted','finished') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `started_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `answers_locked_at` timestamp NULL DEFAULT NULL,
  `scoring_processed_at` timestamp NULL DEFAULT NULL,
  `score` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_attempts_exam_id_user_id_unique` (`exam_id`,`user_id`),
  KEY `exam_attempts_user_id_foreign` (`user_id`),
  CONSTRAINT `exam_attempts_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_attempts`
--

LOCK TABLES `exam_attempts` WRITE;
/*!40000 ALTER TABLE `exam_attempts` DISABLE KEYS */;
INSERT INTO `exam_attempts` VALUES (1,1,4,'finished','2026-02-15 04:06:42','2026-02-15 05:36:45','2026-02-15 05:36:45','2026-02-15 05:36:45',0,'2026-02-15 04:16:42','2026-02-15 05:36:45'),(2,6,4,'finished','2026-02-15 16:36:38','2026-02-15 16:36:54','2026-02-15 16:36:54','2026-02-15 16:36:54',20,'2026-02-15 16:36:38','2026-02-15 16:36:54'),(3,7,4,'finished','2026-02-18 04:31:58','2026-02-18 09:09:26','2026-02-18 09:09:26','2026-02-18 09:09:26',0,'2026-02-18 04:31:58','2026-02-18 09:09:26'),(4,11,4,'finished','2026-02-18 08:01:14','2026-02-18 08:01:37','2026-02-18 08:01:37','2026-02-18 08:01:37',40,'2026-02-18 08:01:14','2026-02-18 08:01:37');
/*!40000 ALTER TABLE `exam_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_options`
--

DROP TABLE IF EXISTS `exam_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exam_question_id` bigint unsigned NOT NULL,
  `option_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exam_options_exam_question_id_foreign` (`exam_question_id`),
  CONSTRAINT `exam_options_exam_question_id_foreign` FOREIGN KEY (`exam_question_id`) REFERENCES `exam_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_options`
--

LOCK TABLES `exam_options` WRITE;
/*!40000 ALTER TABLE `exam_options` DISABLE KEYS */;
INSERT INTO `exam_options` VALUES (1,1,'18',0,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(2,1,'20',1,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(3,1,'22',0,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(4,1,'24',0,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(5,2,'5',0,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(6,2,'6',0,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(7,2,'7',1,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(8,2,'8',0,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(9,3,'12 cm2',0,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(10,3,'24 cm2',0,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(11,3,'30 cm2',0,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(12,3,'36 cm2',1,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(19,5,'gatau',1,'2026-02-15 16:21:28','2026-02-15 16:21:28'),(20,5,'gatau 1',0,'2026-02-15 16:21:28','2026-02-15 16:21:28'),(21,5,'gatau 2',0,'2026-02-15 16:21:28','2026-02-15 16:21:28'),(22,5,'gatau 3',0,'2026-02-15 16:21:28','2026-02-15 16:21:28'),(23,6,'gatau banget',1,'2026-02-15 16:22:01','2026-02-15 16:22:01'),(24,6,'mana saya tau',0,'2026-02-15 16:22:01','2026-02-15 16:22:01'),(25,6,'ya gitu dah',0,'2026-02-15 16:22:01','2026-02-15 16:22:01'),(26,6,'idk',0,'2026-02-15 16:22:01','2026-02-15 16:22:01'),(27,7,'Bulat',0,'2026-02-17 06:30:37','2026-02-17 06:30:37'),(28,7,'Datar',0,'2026-02-17 06:30:37','2026-02-17 06:30:37'),(29,7,'Lonjong',0,'2026-02-17 06:30:37','2026-02-17 06:30:37'),(30,7,'Trapesium',1,'2026-02-17 06:30:37','2026-02-17 06:30:37'),(31,8,'Gatau pak',1,'2026-02-17 06:31:24','2026-02-17 06:31:24'),(32,8,'Saya bukan ilmuwan',0,'2026-02-17 06:31:24','2026-02-17 06:31:24'),(33,8,'Asli gatau',0,'2026-02-17 06:31:24','2026-02-17 06:31:24'),(34,8,'YNKTS',0,'2026-02-17 06:31:24','2026-02-17 06:31:24'),(35,9,'la',1,'2026-02-18 05:46:42','2026-02-18 05:46:42'),(36,9,'lu',0,'2026-02-18 05:46:42','2026-02-18 05:46:42'),(37,9,'lala',0,'2026-02-18 05:46:42','2026-02-18 05:46:42'),(38,9,'lulu',0,'2026-02-18 05:46:42','2026-02-18 05:46:42'),(39,10,'li',1,'2026-02-18 05:47:06','2026-02-18 05:47:06'),(40,10,'lulu',0,'2026-02-18 05:47:06','2026-02-18 05:47:06'),(41,10,'lali',0,'2026-02-18 05:47:06','2026-02-18 05:47:06'),(42,10,'lilu',0,'2026-02-18 05:47:06','2026-02-18 05:47:06'),(59,14,'gatau',1,'2026-02-18 06:31:02','2026-02-18 06:31:02'),(60,14,'babai',0,'2026-02-18 06:31:02','2026-02-18 06:31:02'),(61,14,'lkewo',0,'2026-02-18 06:31:02','2026-02-18 06:31:02'),(62,14,'kjjdofd',0,'2026-02-18 06:31:02','2026-02-18 06:31:02'),(63,11,'owpewop',1,'2026-02-18 06:33:57','2026-02-18 06:33:57'),(64,11,'owoelwllq',0,'2026-02-18 06:33:57','2026-02-18 06:33:57'),(65,11,'lkewo',0,'2026-02-18 06:33:57','2026-02-18 06:33:57'),(66,11,'kewl',0,'2026-02-18 06:33:57','2026-02-18 06:33:57'),(67,12,'gatau',1,'2026-02-18 06:34:05','2026-02-18 06:34:05'),(68,12,'babai',0,'2026-02-18 06:34:05','2026-02-18 06:34:05'),(69,12,'lkewo',0,'2026-02-18 06:34:05','2026-02-18 06:34:05'),(70,12,'welklwk',0,'2026-02-18 06:34:05','2026-02-18 06:34:05'),(79,17,'Bsbdbdb',1,'2026-02-18 07:29:07','2026-02-18 07:29:07'),(80,17,'Bdbdbsb',0,'2026-02-18 07:29:07','2026-02-18 07:29:07'),(81,17,'Hdhehhr',0,'2026-02-18 07:29:07','2026-02-18 07:29:07'),(82,17,'Keelrjjr',0,'2026-02-18 07:29:07','2026-02-18 07:29:07'),(83,18,'Bdbdbrvv',0,'2026-02-18 07:29:23','2026-02-18 07:29:23'),(84,18,'Ndndklrlp',1,'2026-02-18 07:29:23','2026-02-18 07:29:23'),(85,18,'Mdndbfv',0,'2026-02-18 07:29:23','2026-02-18 07:29:23'),(86,18,'Jdkldoke',0,'2026-02-18 07:29:23','2026-02-18 07:29:23'),(87,19,'Hsbbvdvbb',1,'2026-02-18 07:29:39','2026-02-18 07:29:39'),(88,19,'Bdlldoro',0,'2026-02-18 07:29:39','2026-02-18 07:29:39'),(89,19,'Jdhhdhbbrb',0,'2026-02-18 07:29:39','2026-02-18 07:29:39'),(90,19,'Bdbd..jdj',0,'2026-02-18 07:29:39','2026-02-18 07:29:39'),(91,20,'Nshdbdbd',1,'2026-02-18 07:29:54','2026-02-18 07:29:54'),(92,20,'Bsbsbdvdv',0,'2026-02-18 07:29:54','2026-02-18 07:29:54'),(93,20,'Bskppeknd',0,'2026-02-18 07:29:54','2026-02-18 07:29:54'),(94,20,'Bdbbbdbdb',0,'2026-02-18 07:29:54','2026-02-18 07:29:54'),(95,21,'Bshbbsb',1,'2026-02-18 07:30:05','2026-02-18 07:30:05'),(96,21,'Bbbbsbshdh',0,'2026-02-18 07:30:05','2026-02-18 07:30:05'),(97,21,'Kslppejjdbbs',0,'2026-02-18 07:30:05','2026-02-18 07:30:05'),(98,21,'Nsbbd',0,'2026-02-18 07:30:05','2026-02-18 07:30:05');
/*!40000 ALTER TABLE `exam_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_questions`
--

DROP TABLE IF EXISTS `exam_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` bigint unsigned NOT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int unsigned NOT NULL DEFAULT '1',
  `order` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exam_questions_exam_id_foreign` (`exam_id`),
  CONSTRAINT `exam_questions_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_questions`
--

LOCK TABLES `exam_questions` WRITE;
/*!40000 ALTER TABLE `exam_questions` DISABLE KEYS */;
INSERT INTO `exam_questions` VALUES (1,1,'Hasil dari 12 + 8 adalah?',10,1,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(2,1,'Nilai x jika 2x = 14 adalah?',10,2,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(3,1,'Luas persegi dengan sisi 6 cm adalah?',10,3,'2026-02-15 04:16:42','2026-02-15 04:16:42'),(5,6,'Kenapa?',10,1,'2026-02-15 16:21:28','2026-02-15 16:21:28'),(6,6,'kok gitu?',10,2,'2026-02-15 16:22:01','2026-02-15 16:22:01'),(7,7,'Bentuk bumi adalah',10,1,'2026-02-17 06:30:37','2026-02-17 06:30:37'),(8,7,'Jika bumi itu datar, kenapa?',10,2,'2026-02-17 06:31:24','2026-02-17 06:31:24'),(9,8,'lalalallalalu',10,1,'2026-02-18 05:46:42','2026-02-18 05:46:42'),(10,8,'lululililli',10,2,'2026-02-18 05:47:06','2026-02-18 05:47:06'),(11,8,'ksdjkwo',10,3,'2026-02-18 06:08:08','2026-02-18 06:08:08'),(12,8,'kenapa',10,4,'2026-02-18 06:11:37','2026-02-18 06:11:37'),(14,8,'kenapa',10,5,'2026-02-18 06:21:31','2026-02-18 06:21:31'),(17,11,'Hshshejn',10,1,'2026-02-18 07:29:07','2026-02-18 07:29:07'),(18,11,'Hdhsbbsb',10,2,'2026-02-18 07:29:23','2026-02-18 07:29:23'),(19,11,'Hdggejsk',10,3,'2026-02-18 07:29:39','2026-02-18 07:29:39'),(20,11,'Udhhdjdjdm',10,4,'2026-02-18 07:29:54','2026-02-18 07:29:54'),(21,11,'Hshuuehbd',10,5,'2026-02-18 07:30:05','2026-02-18 07:30:05');
/*!40000 ALTER TABLE `exam_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_at` timestamp NOT NULL,
  `end_at` timestamp NOT NULL,
  `authoring_start_at` timestamp NULL DEFAULT NULL,
  `authoring_end_at` timestamp NULL DEFAULT NULL,
  `duration_minutes` int unsigned NOT NULL,
  `status` enum('draft','running','finished') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint unsigned DEFAULT NULL,
  `author_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exams_created_by_foreign` (`created_by`),
  KEY `exams_author_id_foreign` (`author_id`),
  CONSTRAINT `exams_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exams_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exams`
--

LOCK TABLES `exams` WRITE;
/*!40000 ALTER TABLE `exams` DISABLE KEYS */;
INSERT INTO `exams` VALUES (1,'Tryout Matematika Dasar','2026-02-15 03:16:42','2026-02-15 07:16:42','2026-02-15 03:16:42','2026-02-15 07:16:42',45,'finished',1,3,'2026-02-15 04:16:42','2026-02-15 15:57:41'),(6,'Sejarah Kebudayaan Islam','2026-02-15 16:20:00','2026-02-15 17:20:00','2026-02-15 16:20:00','2026-02-15 17:20:00',60,'finished',1,3,'2026-02-15 16:17:58','2026-02-17 05:35:54'),(7,'PJOK','2026-02-18 00:00:00','2026-02-18 17:00:00','2026-02-17 00:00:00','2026-02-17 23:00:00',60,'running',1,3,'2026-02-17 06:28:40','2026-02-18 04:31:42'),(8,'PJOK 2','2026-02-19 00:00:00','2026-02-20 00:00:00','2026-02-18 05:00:00','2026-02-18 08:00:00',60,'running',1,3,'2026-02-18 05:42:54','2026-02-18 08:30:17'),(11,'Ngentot 2','2026-02-18 08:00:00','2026-02-18 10:00:00','2026-02-17 00:00:00','2026-02-18 07:33:00',60,'running',1,3,'2026-02-18 07:28:46','2026-02-18 07:33:14');
/*!40000 ALTER TABLE `exams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_07_074652_create_matches_table',1),(5,'2026_01_08_074600_create_teams_table',1),(6,'2026_01_08_074737_create_questions_table',1),(7,'2026_01_08_074836_create_match_questions_table',1),(8,'2026_01_08_074934_create_buzzes_table',1),(9,'2026_01_08_075020_create_answers_table',1),(10,'2026_02_13_000001_add_role_to_users_table',1),(11,'2026_02_13_000002_update_user_role_to_peserta',1),(12,'2026_02_13_000003_create_exams_table',1),(13,'2026_02_13_000004_create_exam_questions_table',1),(14,'2026_02_13_000005_create_exam_options_table',1),(15,'2026_02_13_000006_create_exam_attempts_table',1),(16,'2026_02_13_000007_create_exam_answers_table',1),(17,'2026_02_13_000008_add_profile_photo_path_to_users_table',1),(18,'2026_02_13_000009_enforce_state_machine_statuses',1),(19,'2026_02_14_000010_create_exam_attempt_audits_table',1),(20,'2026_02_14_000011_normalize_exam_attempt_status_for_sqlite',1),(21,'2026_02_14_000012_drop_legacy_match_tables',1),(22,'2026_02_15_073000_add_author_id_to_exams_table',1),(23,'2026_02_17_000013_add_authoring_window_to_exams_table',2),(24,'2026_02_18_000013_create_security_audits_table',3),(25,'2026_02_18_000014_add_request_context_to_exam_attempt_audits_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_audits`
--

DROP TABLE IF EXISTS `security_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `security_audits_actor_user_id_foreign` (`actor_user_id`),
  KEY `security_audits_action_created_at_index` (`action`,`created_at`),
  KEY `security_audits_target_type_target_id_index` (`target_type`,`target_id`),
  CONSTRAINT `security_audits_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_audits`
--

LOCK TABLES `security_audits` WRITE;
/*!40000 ALTER TABLE `security_audits` DISABLE KEYS */;
INSERT INTO `security_audits` VALUES (1,1,'exam_published','Exam',8,'192.168.0.162','Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0','{\"status\": \"running\"}','2026-02-18 08:30:17','2026-02-18 08:30:17');
/*!40000 ALTER TABLE `security_audits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('eExDmgy9bwKh9xrSBzwGsbcnbP42ocQdKKvjuaWp',1,'192.168.0.162','Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTnZjWmFQY25jV2VCbVI4dFVCNVJEa0tIbG5ORVk2c29EaVJxMk15RiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHA6Ly8xOTIuMTY4LjAuMTkzOjgwMDAvYWRtaW4vdXNlcnM/cm9sZT1hZG1pbiZzZWFyY2g9IjtzOjU6InJvdXRlIjtzOjE3OiJhZG1pbi51c2Vycy5pbmRleCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1771406099),('t9RqpHcdWV0eSAYiySyCcMDDd3R7kPnMwITH8gbu',1,'192.168.0.187','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoic0htTU9RanZmdXN4dmxnQTRESzNBYXhNZWhXMDZQMTFFMkl0ZkVudSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xOTIuMTY4LjAuMTkzOjgwMDAvYWRtaW4vdXNlcnMiO3M6NToicm91dGUiO3M6MTc6ImFkbWluLnVzZXJzLmluZGV4Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1771406133);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'peserta',
  `profile_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@example.com',NULL,'$2y$12$bqWyixensAs2HUm5gV/.IuEd.CzP5pxZnojgo11z7UQlYxq2h8aEm','admin',NULL,NULL,'2026-02-15 04:16:41','2026-02-18 08:59:38'),(2,'Operator','operator@example.com',NULL,'$2y$12$1Af.xyJElbL2MrcFevFzCeAt6328VLNeHiVHyPM5pxKB5TN7DJTeO','operator',NULL,NULL,'2026-02-15 04:16:41','2026-02-18 08:59:47'),(3,'Author','author@example.com',NULL,'$2y$12$eOFoZ1o/V/pKhFdiKhSYoej3.TdVXIPHs.3jUrMxNkiEfR6O7dq0S','author',NULL,NULL,'2026-02-15 04:16:41','2026-02-17 05:50:25'),(4,'purnomo','peserta@example.com',NULL,'$2y$12$TauVbW5vGuB1.pA/8w5BjOoPqd/9leeVDyQ35DyrgQq98DIRqZvUm','peserta',NULL,NULL,'2026-02-15 04:16:42','2026-02-18 08:59:28');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 20:40:14
