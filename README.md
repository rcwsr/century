Century Challenge
=================

Application aimed to automate the process of collecting and collating century 
challenge data for LFCC


Todo:

1.  Users
  * registration
  * ride/{user}
  * add user_id to /add
2. Leaderboard

Getting started:

Install composer (http://getcomposer.org/)

    ./composer.phar install

Create the config file:

    cp config/config.default.yml config/config.yml

Create the database schema:

--
-- Table structure for table `ride`
--

CREATE TABLE IF NOT EXISTS `ride` (
  `ride_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `km` decimal(10,0) DEFAULT NULL,
  `url` mediumtext,
  `points` int(4) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `details` longtext,
  PRIMARY KEY (`ride_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
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
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
