Century Challenge
=================

Application aimed to automate the process of collecting and collating century 
challenge data for LFCC


Install:
----------------
Install dependencies with `php composer update`.

Add your database and Strava API details to `config/config.yml`. Since the app only needs to fetch basic ride data, your
access token is sufficient, and no user auth is required.

Create database `century` and run the SQL below.


```
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `ride` (
  `ride_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `km` decimal(10,1) DEFAULT NULL,
  `average_speed` decimal(10,1) DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `url` mediumtext,
  `date` datetime DEFAULT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` datetime DEFAULT NULL,
  `details` longtext,
  `strava_ride_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`ride_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=794 ;

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `roles` varchar(255) DEFAULT NULL,
  `user_key` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `forum_name` varchar(100) DEFAULT '',
  `active` tinyint(4) DEFAULT '0',
  `strava` varchar(255) DEFAULT '',
  `metric` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=40 ;
```


Todo:
-----
1. Unit tests
2. Validation of strava rides