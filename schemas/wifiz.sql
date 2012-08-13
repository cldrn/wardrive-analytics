SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `wifiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--

CREATE TABLE IF NOT EXISTS `networks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coords` varchar(75) NOT NULL,
  `ssid` varchar(100) NOT NULL,
  `encryption` varchar(45) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `mac_addr` varchar(20) NOT NULL,
  `frequency` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE IF NOT EXISTS `vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mac_identifier` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `known_vulnerabilities` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
