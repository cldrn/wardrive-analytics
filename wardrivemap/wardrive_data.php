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
	* Reads networks from database and returns an array of structures containing the AP info
	* @return mixed
	*/
	function get_networks()
	{
                $networks=array();
		$sql = "SELECT * FROM networks N JOIN vendors V ON N.vendor_id = V.id";
		$results = mysql_query($sql) or die(mysql_error());
	
		if(mysql_num_rows($results)>0) {
			$ = mysql_fetch_assoc($results);
			$ap
		}
		return $networks;
	}
	

?>
