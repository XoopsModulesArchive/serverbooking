CREATE TABLE `team_server` (
  `serverid` mediumint(8) unsigned NOT NULL auto_increment,
  `servername` varchar(32),
  `serverip` varchar(20),
  `serverport` mediumint(8),
  `is_bookable` tinyint(4),
  `serverzone` tinyint(4),
  `region` varchar(20),
  
  PRIMARY KEY  (`serverid`),
  UNIQUE KEY `serverid` (`serverid`),
  KEY `serverid_2` (`serverid`)
);