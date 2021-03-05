// create the admins table
// populate it manually derived from users table in old app

CREATE TABLE `admins` (
  `adminid` int(11) NOT NULL AUTO_INCREMENT,
  `effective_start_ts` datetime NOT NULL,
  `effective_end_ts` datetime DEFAULT NULL,
  `updated_by` varchar(32) NOT NULL,
  `uid` int(11) NOT NULL,
  `login` varchar(32) NOT NULL,
  `name` varchar(80) NOT NULL,
  `password` varchar(80) NOT NULL,
  `start_ts` datetime NOT NULL,
  `end_ts` datetime DEFAULT NULL,
  `level` int(11) DEFAULT '1',
  `email` varchar(80) NOT NULL,
  PRIMARY KEY (`adminid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

// replace debug table, contents initially empty.

drop table debug;
CREATE TABLE `debug` (
  `dbgid` int(11) NOT NULL AUTO_INCREMENT,
  `datedone` datetime DEFAULT NULL,
  `message` text,
  `trace` text,
  `dbgsession` int(11) DEFAULT NULL,
  PRIMARY KEY (`dbgid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

// dictionary table already exists,  just need to update contents
//  generate via mysqldump -u root -c --skip-extended-insert koikoi dictionary
//  include those with dictid above 4

INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (5,'sponsor_state','Available','N','child is not sponsored',10);
INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (6,'sponsor_state','Pending','P','Sponsorship is pending',20);
INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (7,'sponsor_state','Sponsored','Y','Child is sponsored',30);
INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (8,'yesno','Yes','Y','Std yes/no form',10);
INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (9,'yesno','No','N','Std yes/no form',20);
INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (10,'overdue','year','15','threshold for being considered overdue',10);
INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (11,'overdue','month','15','threshold for being considered overdue',20);
INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (12,'overdue','week','7','threshold for being considered overdue',30);
INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (13,'login','max_fails','6','maximum failures to trigger a security warning',10);
INSERT INTO `dictionary` (`dictid`, `area`, `label`, `setting`, `comment`, `seq`) VALUES (14,'login','max_idle','30','maximum minutes a user can be idle',20);

// email is a temp table for passing data to civicrm
// initial contents are empty

CREATE TABLE `email` (
  `emailid` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL,
  `child` varchar(32) DEFAULT NULL,
  `childid` int(11) DEFAULT NULL,
  `last_date` date DEFAULT NULL,
  `datedone` datetime DEFAULT NULL,
  PRIMARY KEY (`emailid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

// errors table is new and should start empty

CREATE TABLE `errors` (
  `errorid` int(11) NOT NULL AUTO_INCREMENT,
  `datedone` datetime DEFAULT NULL,
  `severity` int(11) DEFAULT NULL,
  `message` text,
  `trace` text,
  `ip` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`errorid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

// forms contains form definitions, import table and contents
// mysqldump -u root koikoi forms > /tmp/forms.sql    then load into production.

// items
//   new fields added, update the new fields

  alter table items add column dob date;
  alter table items add column is_sponsored enum('N','P','Y') default 'N';
  alter table items add column sponsorid int;

  update items set is_sponsored = 'Y' where itemid in (
  select distinct itemid from sponsorships where effective_end_ts is null) ;

  insert items (itemid, sponsorid)
  (select itemid, sponsorid  from sponsorships where effective_end_ts is null order by itemid,sponsorid)
  on duplicate key update sponsorid = values(sponsorid) ;

// log   obsolete and empty... drop this table
drop table log;

// logs   new table, contents empty
CREATE TABLE `logs` (
  `logid` int(11) NOT NULL AUTO_INCREMENT,
  `datedone` datetime DEFAULT NULL,
  `event_code` varchar(32) DEFAULT NULL,
  `detail` text,
  `security` enum('Y','N') DEFAULT 'N',
  `ip` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

// metainfo  -- skip this...  it's not used.

// sessions  new table, contents empty

CREATE TABLE `sessions` (
  `sessionid` varchar(80) NOT NULL,
  `uid` int(11) NOT NULL,
  `datedone` datetime DEFAULT NULL,
  `ip` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`sessionid`),
  UNIQUE KEY `uid` (`uid`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


// sponsors  -- carried forward from version 1 but being abandoned.
//   don't dare drop it right now...
//   no changes to prior table

// sponsorships -- new fields added and need to be updated

alter table sponsorships add column email varchar(80);
alter table sponsorships add column first_name varchar(80);
alter table sponsorships add column middle_initial varchar(2);
alter table sponsorships add column last_name varchar(80);
alter table sponsorships add column start_date date;

insert sponsorships (id,email,first_name,middle_initial,last_name,start_date) 
  (select id, b.email,b.first_name, b.middle_initial,b.last_name, date(a.effective_start_ts) as start_date 
  from sponsorships a, sponsors b where a.sponsorid = b.sponsorid)
  on duplicate key update email = values(email);
insert sponsorships (id,email,first_name,middle_initial,last_name,start_date) 
  (select id, b.email,b.first_name, b.middle_initial,b.last_name, date(a.effective_start_ts) as start_date 
  from sponsorships a, sponsors b where a.sponsorid = b.sponsorid)
  on duplicate key update first_name = values(first_name);
insert sponsorships (id,email,first_name,middle_initial,last_name,start_date) 
  (select id, b.email,b.first_name, b.middle_initial,b.last_name, date(a.effective_start_ts) as start_date 
  from sponsorships a, sponsors b where a.sponsorid = b.sponsorid)
  on duplicate key update middle_initial = values(middle_initial);
insert sponsorships (id,email,first_name,middle_initial,last_name,start_date) 
  (select id, b.email,b.first_name, b.middle_initial,b.last_name, date(a.effective_start_ts) as start_date 
  from sponsorships a, sponsors b where a.sponsorid = b.sponsorid)
  on duplicate key update last_name = values(last_name);
insert sponsorships (id,email,first_name,middle_initial,last_name,start_date) 
  (select id, b.email,b.first_name, b.middle_initial,b.last_name, date(a.effective_start_ts) as start_date 
  from sponsorships a, sponsors b where a.sponsorid = b.sponsorid)
  on duplicate key update start_date = values(start_date);
  
  

// sponsorship_pending  -- new table, empty contents

CREATE TABLE `sponsorship_pending` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itemid` int(11) NOT NULL,
  `email` varchar(80) DEFAULT NULL,
  `first_name` varchar(80) DEFAULT NULL,
  `middle_initial` varchar(2) DEFAULT NULL,
  `last_name` varchar(80) DEFAULT NULL,
  `appear` enum('Y','N') NOT NULL DEFAULT 'Y',
  `appear_text` varchar(80) DEFAULT NULL,
  `datedone` datetime DEFAULT NULL,
  `is_locked` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`),
  UNIQUE KEY `itemid` (`itemid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

// users  -- being phased out, retain for now.
//  no changes to the table

// videos  -- unchanged
//  no changes to table

// tested and debugged 7/8/2013

