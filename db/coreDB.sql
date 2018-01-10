
DROP DATABASE framework;
DROP USER frameuser@localhost;

CREATE DATABASE IF NOT EXISTS framework;

USE framework;

CREATE USER 'frameuser'@'localhost' IDENTIFIED BY 'framepass';
GRANT ALL PRIVILEGES ON *.* TO 'frameuser'@'localhost';

--
-- Table structure for table UserGroupPrivRef
--

CREATE TABLE UserGroupPrivRef (
  groupId int(11) NOT NULL,
  privKey varchar(24) NOT NULL,
  UNIQUE KEY idx1 (groupId,privKey),
  KEY roleId (groupId)
) ENGINE=InnoDB COMMENT='This table associates Roles to Privs';

INSERT INTO UserGroupPrivRef VALUES (1,'changePass'),(1,'HelloHtmlNav'),(1,'HelloJsonNav');

--
-- Table structure for table UserGroupRef
--

CREATE TABLE UserGroupRef (
  userId int(11) NOT NULL,
  groupId int(11) NOT NULL,
  KEY user_idx (userId)
) ENGINE=InnoDB;

INSERT INTO UserGroupRef VALUES (10,1);

--
-- Table structure for table UserGroups
--

CREATE TABLE UserGroups (
  id      int(11)      NOT NULL AUTO_INCREMENT,
 `key`    varchar(10)  NOT NULL,
  name    varchar(128) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE  KEY abbrev (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2;

INSERT INTO UserGroups VALUES (1,'base','Basic user');

--
-- Table structure for table UserPrivKeys
--

CREATE TABLE UserPrivKeys (
  userId  int(11)     NOT NULL,
  privKey varchar(24) NOT NULL
) ENGINE=InnoDB;

INSERT INTO UserPrivKeys VALUES (10,'changePass'),(10,'HelloHtmlNav'),(10,'HelloJsonNav');

--
-- Table structure for table UserPrivs
--

CREATE TABLE UserPrivs (
  id int(11) NOT NULL AUTO_INCREMENT,
  privKey varchar(24) NOT NULL,
  name varchar(128) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY key_idx (privKey)
) ENGINE=InnoDB AUTO_INCREMENT=5;

--
-- Dumping data for table UserPrivs
--

INSERT INTO UserPrivs VALUES (1,'changePass','Change password'),(2,'editUser','Edit users'),(3,'HelloHtmlNav','HelloHtml navigation menu item'),(4,'HelloJsonNav','HelloJson navigation menu item');

--
-- Table structure for table Users
--

CREATE TABLE Users (
  id int(11) NOT NULL AUTO_INCREMENT,
  fullName varchar(45) DEFAULT NULL,
  handle varchar(45) NOT NULL,
  password varchar(64) NOT NULL,
  password_save varchar(64) DEFAULT NULL,
  email varchar(128) NOT NULL,
  status enum('inactive','active','admin') NOT NULL DEFAULT 'active',
  userLevel int(4) NOT NULL DEFAULT '10',
  joinDate date NOT NULL,
  lastAccess datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=11;

--
-- Dumping data for table Users
--

INSERT INTO Users VALUES 
(1,'Joe Example','example','password','','foobar@example.com','admin',30,'2010-09-03','2010-09-03 23:23:28'),
(10,'Some Tech Guy','sometech','123tech',NULL,'mrtech@example.com','active',10,'2013-08-09','2010-09-03 23:23:28');


