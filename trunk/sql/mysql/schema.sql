CREATE TABLE `ip_group_country` (
  `ip_start` bigint(20) NOT NULL,
  `ip_cidr` varchar(20) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `country_name` varchar(64) NOT NULL,
  UNIQUE KEY `ip_start` (`ip_start`),
  KEY `country` (`country_code`)
) ENGINE=InnodDB DEFAULT CHARSET=utf8;

CREATE TABLE `ip_group_city` (
  `ip_start` bigint(20) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `country_name` varchar(64) NOT NULL,
  `region_code` varchar(2) NOT NULL,
  `region_name` varchar(64) NOT NULL,
  `city` varchar(64) NOT NULL,
  `zipcode` varchar(6) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `metrocode` varchar(3) NOT NULL,
  UNIQUE KEY `ip_start` (`ip_start`)
) ENGINE=InnodDB DEFAULT CHARSET=utf8;

