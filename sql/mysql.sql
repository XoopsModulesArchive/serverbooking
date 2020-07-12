CREATE TABLE `server_bookings` (
   `bookid` int(12) unsigned NOT NULL auto_increment,
   `begin` int(10),
   `end` int(10),
   `booker` text,
   `wonid` int(11),
   `bookerid` int(12),
   `bookeremail` text,
   `irc` mediumtext,
   `opponent` text,
   `matchtype` text NOT NULL,
   `status` text NOT NULL,
   `admin` smallint(6),
   `bookcomments` text,
   `serverid` int(11),
   PRIMARY KEY (`bookid`),
   UNIQUE `bookid` (`bookid`),
   KEY `bookid_2` (`bookid`)
);

CREATE TABLE `server_serveradmins` (
  `said` mediumint(11) unsigned NOT NULL auto_increment,
  `uid` int(12) unsigned NOT NULL default '0',
  `serverid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`said`),
  UNIQUE KEY `said` (`said`)
) TYPE=MyISAM;

CREATE TABLE `server_bookers` (
   `uid` int(12) NOT NULL,
   `irc` text,
   `wonid` int(12),
   PRIMARY KEY (`uid`),
   UNIQUE `uid` (`uid`)
);

CREATE TABLE `server_rules` (
   `ruleid` int(12) NOT NULL auto_increment,
   `weekday` tinyint(4),
   `begin` tinyint(4),
   `end` tinyint(4),
   `serverid` int(11) NOT NULL,
   `reason` text,
   PRIMARY KEY (`ruleid`),
   UNIQUE `ruleid` (`ruleid`)
);

CREATE TABLE `server_scrimm` (
   `scrimmid` int(12) NOT NULL auto_increment,
   `begin` int(12) NOT NULL,
   `teamid` int(12) NOT NULL,
   `teamadm` int(12),
   `status` tinyint(2) NOT NULL default '0',
   `pref` int(12),
   PRIMARY KEY (`scrimmid`),
   UNIQUE KEY `scrimmid` (`scrimmid`)
);

CREATE TABLE `server_scrimm_reply` (
    `scrimmid` int(12) NOT NULL,
    `serverteamid` int(12) NOT NULL,
    PRIMARY KEY (`scrimmid`,`serverteamid`)
);

CREATE TABLE `server_team` (
   `serverteamid` int(12) NOT NULL auto_increment,
   `name` varchar(35),
   `irc` varchar(25),
   `tag` varchar(10),
   `homepage` varchar(120),
   PRIMARY KEY (`serverteamid`),
   UNIQUE KEY `serverteamid` (`serverteamid`)
);

CREATE TABLE `server_teamadmins` (
   `uid` int(12) NOT NULL default '0',
   `serverteamid` int(12) NOT NULL default '0',
   PRIMARY KEY (`uid`,`serverteamid`)
);