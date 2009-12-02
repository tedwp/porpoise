CREATE TABLE `POI` (
  `id` int(11) NOT NULL,
  `attribution` varchar(255) default NULL,
  `imageURL` varchar(1024) default NULL,
  `lat` float default NULL,
  `lon` float default NULL,
  `line2` varchar(255) default NULL,
  `line3` varchar(255) default NULL,
  `line4` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Action` (
  `id` int(11) NOT NULL,
  `uri` varchar(1024) default NULL,
  `label` varchar(255) default NULL,
  `poiId` int(11) default NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY `poiId` (`poiId`) REFERENCES POI (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

insert into POI(
type,title,line2,line3,line4,lat,lon,attribution
) VALUES
('1','Example POI','line2 here','line3 here','line4 here','52.090541','5.112068','attribution here');
