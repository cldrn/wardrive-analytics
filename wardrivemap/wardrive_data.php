<?php
/*
* Reads the database and returns JSON data for all the available networks
*
* @see wifiz.sql
*/
	define("DB_USER","root");
	define("DB_DBNAME","wifiz");
	define("DB_PWD","toor");
	define("DEBUG_LEVEL", 1);
	define("DEBUG_LOG", "debug.log");

        /**
	* Inits database
	*/
	function init_db() {
		$con = mysql_connect("localhost", DB_USER, DB_PWD);
		if(!$con) {	die(mysql_error()); }
		mysql_select_db(DB_DBNAME);
	}
	/**
	* Writes string to logfile and stdout
	*/
	function debug_log($string) 
	{
		if (DEBUG_LEVEL>0)
			echo "\n[DEBUG] $string\n";
		
		$fp=fopen(DEBUG_LOG,"a");
		fwrite($fp,"{$string}\n");
		fclose($fp);
	}
	
	/**
	* Writes string to logfile and stdout
	*/
	function debug_ap($ap) 
	{
		if (DEBUG_LEVEL>0)
		{
			echo "[Network]\n";
			echo "Vendor name:".$ap["vendor"]."\n";
			echo "SSID:".$ap["ssid"]."\n";
                        echo "Coordinates:".$ap["coords"]."\n";
			echo "Encryption:".$ap["encryption"]."\n";
			echo "MAC:".$ap["mac_addr"]."\n";
			echo "Frequency:".$ap["frequency"]."\n\n";
		}
	}
        /**
	* Get a network count
	* @return mixed
	*/
	function get_network_count()
	{
                $networks=array();
		$sql = "SELECT count(*) as network_count FROM networks";
		$results = mysql_query($sql) or die(mysql_error());
		$data = mysql_fetch_assoc($results);
               
		return $data["network_count"];
	}

	/**
	* Generate json data for networks in database
	* @return string
	*/
	function generate_json()
	{

        }
	
	/**
	* Reads networks from database and returns an array of structures containing the AP info
	* @return mixed
	*/
	function get_networks()
	{
                $networks=array();
		$sql = "SELECT V.name as vendor_name, V.known_vulnerabilities, N.coords, N.ssid, N.encryption, N.frequency, N.mac_addr FROM networks N JOIN vendors V ON N.vendor_id = V.id";
		$results = mysql_query($sql) or die(mysql_error());
	        $i=0;
		if(mysql_num_rows($results)>0) {
			$data = mysql_fetch_assoc($results);

			$networks[$i]["vendor"]=$data["vendor_name"];
                        $networks[$i]["ssid"]=$data["ssid"];
                        $networks[$i]["coords"]=$data["coords"];
                        $networks[$i]["encryption"]=$data["encryption"];
                        $networks[$i]["mac_addr"]=$data["mac_addr"];
                        $networks[$i]["frequency"]=$data["frequency"];
                        debug_ap($networks[$i]);
                        $i++;
		}
		return $networks;
	}

	/**
	* Generate json data for networks in database
	* @return string
	*/
	function generate_json()
	{

        }
?>
