-- MySQL dump 10.13  Distrib 6.0.9-alpha, for apple-darwin9.5.0 (i386)
--
-- Host: localhost    Database: partuza
-- ------------------------------------------------------
-- Server version	6.0.9-alpha

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
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  `title` mediumtext NOT NULL,
  `body` mediumtext NOT NULL,
  `created` int(11) NOT NULL,
  KEY `id` (`id`),
  KEY `activity_stream_id` (`person_id`),
  KEY `created` (`created`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `media_items`
--

DROP TABLE IF EXISTS `media_items`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `media_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) DEFAULT NULL,
  `album_id` int(11) DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `mime_type` char(64) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `created` int(11) NOT NULL,
  `last_updated` int(11) DEFAULT NULL,
  `language` char(64) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `num_comments` int(11) DEFAULT NULL,
  `num_views` int(11) DEFAULT NULL,
  `num_votes` int(11) DEFAULT NULL,
  `rating` tinyint DEFAULT NULL,
  `start_time` char(64) DEFAULT NULL,
  `title` char(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `tagged_people` text DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `thumbnail_url` char(128) DEFAULT NULL,
  `type` enum('AUDIO','IMAGE','VIDEO') NOT NULL,
  `url` char(128) NOT NULL,
  `app_id` int(11) DEFAULT 0,
  KEY `id` (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `album_id` (`album_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `albums`
--

DROP TABLE IF EXISTS `albums`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `media_mime_type` char(64) DEFAULT NULL,
  `media_type` enum('AUDIO','IMAGE','VIDEO') NOT NULL,
  `thumbnail_url` char(128) DEFAULT NULL,
  `app_id` int(11) DEFAULT 0,
  `created` int(11),
  `modified` int(11),
  `media_count` int(11) DEFAULT 0,
  `media_id` int(11),
  KEY `id` (`id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` char(128) DEFAULT NULL,
  `extended_address` char(128) DEFAULT NULL,
  `latitude` int(11) DEFAULT NULL,
  `locality` varchar(128) DEFAULT NULL,
  `longitude` int(11) DEFAULT NULL,
  `po_box` char(32) DEFAULT NULL,
  `postal_code` char(32) DEFAULT NULL,
  `region` char(64) DEFAULT NULL,
  `street_address` char(128) DEFAULT NULL,
  `address_type` char(128) DEFAULT NULL,
  `unstructured_address` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `application_settings`
--

DROP TABLE IF EXISTS `application_settings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `application_settings` (
  `application_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `name` char(128) NOT NULL,
  `value` char(255) NOT NULL,
  UNIQUE KEY `application_id` (`application_id`,`person_id`,`name`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` char(128) NOT NULL,
  `title` char(128) DEFAULT NULL,
  `directory_title` varchar(128) DEFAULT NULL,
  `screenshot` char(128) DEFAULT NULL,
  `thumbnail` char(128) DEFAULT NULL,
  `author` char(128) DEFAULT NULL,
  `author_email` char(128) DEFAULT NULL,
  `description` mediumtext,
  `settings` mediumtext,
  `views` mediumtext,
  `version` varchar(64) NOT NULL,
  `height` int(11) NOT NULL DEFAULT '0',
  `scrolling` int(11) NOT NULL DEFAULT '0',
  `approved` enum('Y','N') DEFAULT 'N',
  `modified` int(11) NOT NULL,
  UNIQUE KEY `url` (`url`),
  KEY `id` (`id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `authenticated`
--

DROP TABLE IF EXISTS `authenticated`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `authenticated` (
  `person_id` int(11) NOT NULL,
  `hash` varchar(41) NOT NULL,
  PRIMARY KEY (`hash`),
  UNIQUE KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `friend_requests`
--

DROP TABLE IF EXISTS `friend_requests`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `friend_requests` (
  `person_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  UNIQUE KEY `person_id` (`person_id`,`friend_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `friends` (
  `person_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  UNIQUE KEY `person_id` (`person_id`,`friend_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(4) DEFAULT NULL,
  `name` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `title` char(255) NOT NULL,
  `body` mediumtext DEFAULT NULL,
  `app_id` int(11) NOT NULL,
  `body_id` char(32) DEFAULT NULL,
  `title_id` char(32) DEFAULT NULL,
  `in_reply_to` int(11) DEFAULT NULL,
  `replies` mediumtext DEFAULT NULL,
  `status` enum('new','read', 'deleted') DEFAULT 'new',
  `type` enum('email','notification', 'private_message', 'public_message') DEFAULT 'private_message',
  `recipients` mediumtext DEFAULT NULL,
  `collection_ids` mediumtext DEFAULT NULL,
  `urls` mediumtext DEFAULT NULL,
  `updated` int(11) NOT NULL,
  `to_deleted` enum('yes','no') DEFAULT 'no',
  `from_deleted` enum('yes','no') DEFAULT 'no',
  `created` int(11) NOT NULL,
  KEY `id` (`id`),
  KEY `to` (`to`,`created`),
  KEY `from` (`from`,`created`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `message_collections`
--

DROP TABLE IF EXISTS `message_collections`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `message_collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  `title` char(255) NOT NULL,
  `updated` int(11) NOT NULL,
  `urls` mediumtext DEFAULT NULL,
  `created` int(11) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `message_collections`
--

DROP TABLE IF EXISTS `message_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `message_groups` (
  `message_id` int(11) NOT NULL,
  `message_collection_id` int(11) NOT NULL,
  UNIQUE KEY `message_id` (`message_id`,`message_collection_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `oauth_consumer`
--

DROP TABLE IF EXISTS `oauth_consumer`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `oauth_consumer` (
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `app_id` int(11) NOT NULL DEFAULT '0',
  `consumer_key` char(64) NOT NULL,
  `consumer_secret` char(64) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `app_id` (`app_id`),
  KEY `consumer_key` (`consumer_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `oauth_nonce`
--

DROP TABLE IF EXISTS `oauth_nonce`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `oauth_nonce` (
  `nonce` char(64) NOT NULL,
  `nonce_timestamp` int(11) NOT NULL,
  PRIMARY KEY (`nonce`),
  KEY `nonce_timestamp` (`nonce_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `oauth_token`
--

DROP TABLE IF EXISTS `oauth_token`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `oauth_token` (
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `consumer_key` char(64) NOT NULL,
  `type` char(7) NOT NULL,
  `token_key` char(64) NOT NULL,
  `token_secret` char(64) NOT NULL,
  `authorized` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`token_key`),
  UNIQUE KEY `token_key` (`token_key`),
  KEY `token_key_2` (`token_key`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) DEFAULT NULL,
  `description` mediumtext,
  `end_date` int(11) DEFAULT NULL,
  `field` char(128) DEFAULT NULL,
  `name` char(128) DEFAULT NULL,
  `salary` char(64) DEFAULT NULL,
  `start_date` int(11) DEFAULT NULL,
  `sub_field` char(64) DEFAULT NULL,
  `title` char(64) DEFAULT NULL,
  `webpage` char(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_activities`
--

DROP TABLE IF EXISTS `person_activities`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_activities` (
  `person_id` int(11) NOT NULL,
  `activity` char(128) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_addresses`
--

DROP TABLE IF EXISTS `person_addresses`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_addresses` (
  `person_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_applications`
--

DROP TABLE IF EXISTS `person_applications`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`),
  KEY `application_id` (`application_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_body_type`
--

DROP TABLE IF EXISTS `person_body_type`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_body_type` (
  `person_id` int(11) NOT NULL,
  `build` char(64) DEFAULT NULL,
  `eye_color` char(64) DEFAULT NULL,
  `hair_color` char(64) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_books`
--

DROP TABLE IF EXISTS `person_books`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_books` (
  `person_id` int(11) NOT NULL,
  `book` char(128) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_cars`
--

DROP TABLE IF EXISTS `person_cars`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_cars` (
  `person_id` int(11) NOT NULL,
  `car` char(128) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_current_location`
--

DROP TABLE IF EXISTS `person_current_location`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_current_location` (
  `person_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_emails`
--

DROP TABLE IF EXISTS `person_emails`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_emails` (
  `person_id` int(11) NOT NULL,
  `address` char(128) NOT NULL,
  `email_type` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_food`
--

DROP TABLE IF EXISTS `person_food`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_food` (
  `person_id` int(11) NOT NULL,
  `food` char(128) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_heroes`
--

DROP TABLE IF EXISTS `person_heroes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_heroes` (
  `person_id` int(11) NOT NULL,
  `hero` char(128) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_interests`
--

DROP TABLE IF EXISTS `person_interests`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_interests` (
  `person_id` int(11) NOT NULL,
  `intrest` char(128) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_jobs`
--

DROP TABLE IF EXISTS `person_jobs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_jobs` (
  `person_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_languages_spoken`
--

DROP TABLE IF EXISTS `person_languages_spoken`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_languages_spoken` (
  `person_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_movies`
--

DROP TABLE IF EXISTS `person_movies`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_movies` (
  `person_id` int(11) NOT NULL,
  `movie` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_music`
--

DROP TABLE IF EXISTS `person_music`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_music` (
  `person_id` int(11) NOT NULL,
  `music` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_phone_numbers`
--

DROP TABLE IF EXISTS `person_phone_numbers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_phone_numbers` (
  `person_id` int(11) NOT NULL,
  `number` char(64) DEFAULT NULL,
  `number_type` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_quotes`
--

DROP TABLE IF EXISTS `person_quotes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_quotes` (
  `person_id` int(11) NOT NULL,
  `quote` mediumtext,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_schools`
--

DROP TABLE IF EXISTS `person_schools`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_schools` (
  `person_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_sports`
--

DROP TABLE IF EXISTS `person_sports`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_sports` (
  `person_id` int(11) NOT NULL,
  `sport` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_tags`
--

DROP TABLE IF EXISTS `person_tags`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_tags` (
  `person_id` int(11) NOT NULL,
  `tag` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_turn_offs`
--

DROP TABLE IF EXISTS `person_turn_offs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_turn_offs` (
  `person_id` int(11) NOT NULL,
  `turn_off` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_turn_ons`
--

DROP TABLE IF EXISTS `person_turn_ons`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_turn_ons` (
  `person_id` int(11) NOT NULL,
  `turn_on` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_tv_shows`
--

DROP TABLE IF EXISTS `person_tv_shows`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_tv_shows` (
  `person_id` int(11) NOT NULL,
  `tv_show` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `person_urls`
--

DROP TABLE IF EXISTS `person_urls`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `person_urls` (
  `person_id` int(11) NOT NULL,
  `url` char(128) DEFAULT NULL,
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `persons`
--

DROP TABLE IF EXISTS `persons`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `persons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `password` char(128) NOT NULL,
  `about_me` mediumtext,
  `age` int(11) DEFAULT NULL,
  `children` mediumtext,
  `date_of_birth` int(11) DEFAULT NULL,
  `drinker` enum('HEAVILY','NO','OCCASIONALLY','QUIT','QUITTING','REGULARLY','SOCIALLY','YES') DEFAULT NULL,
  `ethnicity` char(128) DEFAULT NULL,
  `fashion` mediumtext,
  `gender` enum('MALE','FEMALE') DEFAULT NULL,
  `happiest_when` mediumtext,
  `humor` mediumtext,
  `job_interests` mediumtext,
  `living_arrangement` mediumtext,
  `looking_for` mediumtext,
  `nickname` char(128) DEFAULT NULL,
  `pets` mediumtext,
  `political_views` mediumtext,
  `profile_song` char(128) DEFAULT NULL,
  `profile_url` char(128) DEFAULT NULL,
  `profile_video` char(128) DEFAULT NULL,
  `relationship_status` char(128) DEFAULT NULL,
  `religion` char(128) DEFAULT NULL,
  `romance` char(128) DEFAULT NULL,
  `scared_of` mediumtext,
  `sexual_orientation` char(128) DEFAULT NULL,
  `smoker` enum('HEAVILY','NO','OCCASIONALLY','QUIT','QUITTING','REGULARLY','SOCIALLY','YES') DEFAULT NULL,
  `status` char(128) DEFAULT NULL,
  `thumbnail_url` char(128) DEFAULT NULL,
  `time_zone` int(11) DEFAULT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `uploaded_size` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `nickname` (`nickname`),
  KEY `email` (`email`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-02-03 16:50:02
