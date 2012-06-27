<?php
/*
* Imports .kml files into a mysql database.
*
* Note: This script queries hwaddress.com to get the vendor of the wireless networks if the vendor does not exists in the table "vendors".
* Depends on curl.
* @see wifiz.sql
*/
	define("DB_USER","root");
	define("DB_DBNAME","wifiz");
	define("DB_PWD","toor");
	define("DEBUG_LEVEL", 1);
	define("DEBUG_LOG", "debug.log");
	define("VENDORS_LOOKUP_URI","http://hwaddress.com/?q=");
	define("HTTP_USERAGENT", "Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.7a) Gecko/20040614 Firefox/0.9.0+");

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
	*HTTP GET
	*@param string $url URL to the file
	*@param string $ref Referer
	*@return mixed
	*/ 
	function http_get($url, $userAgent=HTTP_USERAGENT)
	{
		$curl_obj = curl_init();
		
		curl_setopt($curl_obj, CURLOPT_URL, $url);      
		curl_setopt($curl_obj, CURLOPT_REFERER, ""); 		
		curl_setopt($curl_obj, CURLOPT_HEADER, TRUE);
		curl_setopt($curl_obj, CURLOPT_HTTPGET, TRUE);       
		curl_setopt($curl_obj, CURLOPT_SSL_VERIFYPEER, FALSE);   
		curl_setopt($curl_obj, CURLOPT_FOLLOWLOCATION, TRUE); //followlocation cannot be used when safe_mode/open_basedir are on
		curl_setopt($curl_obj, CURLOPT_MAXREDIRS, 1);           
		curl_setopt($curl_obj, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($curl_obj, CURLOPT_TIMEOUT, 5);  
		curl_setopt($curl_obj, CURLOPT_USERAGENT, $userAgent);

    		$page   = curl_exec($curl_obj); 
 		$err = curl_error($curl_obj);
 		
 		curl_close($curl_obj);
 		
 		if(strlen($err) > 0) {
 			return -1;
 		} else
 		{
    			return $page;
    		}    
	}
	
	/**
	*Returns string between start_str and end_str
	*@param string $string Haystack
	*@param string $start_str
	*@param string $end_str
	*@return string String between delimitiers	
	*/
	function return_substr($string, $start_delimiter, $end_delimiter)
	{
		$offStart = strpos($string, $start_delimiter);
		$Lstart = strlen($start_delimiter);
		$offEnd = strpos($string, $end_delimiter, $offStart);
	
		if($offStart === false || $offEnd === false)
		{
			return false;
		}
		
		return substr($string, ($offStart+$Lstart), ($offEnd-($offStart+$Lstart)));
	}	
	
	/**
	* Searches for the vendor id using the vendor name
	* @param string $vendor_string Vendor name [String]
	* @return int Vendor id
	*/
	function lookup_vendor($mac_addr) 
	{
		//Extracts mac identifier from full mac address
		$mac_identifier=str_replace(":","",$mac_addr);
		$mac_identifier=substr($mac_identifier,0,6);
		$mac_identifier=strtoupper($mac_identifier);
		
		if (!is_vendor_registered($mac_identifier)) 
		{
			debug_log("HTTP:".VENDORS_LOOKUP_URI.$mac_addr);
			$response = http_get(VENDORS_LOOKUP_URI.$mac_addr);
			$mac_delimeter="/mac/{$mac_identifier}-000000.html";
                        $vendor=return_substr($response, '<td><a href="'.$mac_delimeter.'">',"</a></td>");
			insert_vendor($vendor, $mac_identifier);
                        debug_log("HTTP:Vendor found:".$vendor);
			return $vendor;
		} else {
                        $vendor = get_vendor_name_by_mac($mac_identifier);
                        debug_log("DB HIT!:Vendor:".$vendor); 
			return $vendor;
		}
	}
	
	/**
	* Searches for the vendor id using the vendor name
	* @param string $vendor_string Vendor name [String]
	* @return int Vendor id
	*/
	function get_vendor_id_by_name($vendor_string) 
	{
		$vendor_string = mysql_real_escape_string($vendor_string);
		$sql = "SELECT * FROM vendors WHERE name='$vendor_string'";
		$results = mysql_query($sql) or die(mysql_error());
	
		if(mysql_num_rows($results)>0) {
			$vendor = mysql_fetch_assoc($results);
			return $vendor["id"];
		}
		return -1;
	}
	
	/**
	* Searches for the vendor name using the mac address identifier
	* @param string $vendor_string Vendor name [String]
	* @return int Vendor id
	*/
	function get_vendor_name_by_mac($mac) 
	{
		$mac = mysql_real_escape_string($mac);
		$sql = "SELECT name FROM vendors WHERE mac_identifier='$mac'";
		$results = mysql_query($sql) or die(mysql_error());
	
		if(mysql_num_rows($results)>0) {
			$vendor = mysql_fetch_assoc($results);
			return $vendor["name"];
		}
		return -1;
	}
	
	/**
	* Inserts vendor in database
	* @param string $vendor_string Vendor name [String]
	* @return boolean Insert status
	*/
	function insert_vendor($name, $identifier, $vulns_known=false) 
	{
		$vulns = 0;
		$vendor_string = mysql_real_escape_string($name);
		$id_string = mysql_real_escape_string($identifier);
		if( $vulns_known )
			$vulns=1;
		$sql = "INSERT INTO vendors (name, mac_identifier, known_vulnerabilities) VALUES('$vendor_string','$id_string',$vulns)";
		$results = mysql_query($sql) or die(mysql_error());
		
		return true;
	}
	
	/**
	* Inserts network in database
	* @return boolean Insert status
	*/
	function insert_network($coords, $ssid, $encryption, $vendor_id, $mac_addr, $frequency) 
	{
		$coords = mysql_real_escape_string($coords);
		$ssid = mysql_real_escape_string($ssid);
		$encryption = mysql_real_escape_string($encryption);
		$vendor_id = mysql_real_escape_string($vendor_id);
		$mac_addr = mysql_real_escape_string($mac_addr);
		$frequency = mysql_real_escape_string($frequency);
		
		$sql = "INSERT INTO networks (coords, ssid, encryption, vendor_id, mac_addr, frequency, created) VALUES('$coords','$ssid','$encryption','$vendor_id','$mac_addr','$frequency', NOW())";
		$results = mysql_query($sql) or die(mysql_error());
		
		return true;
	}
	
	/**
	* Checks if the wireless network is already on the db
	* @param 
	* @return
	*/
	function is_network_registered($coords, $mac_addr) 
	{
		$coords = mysql_real_escape_string($coords);
		$mac_addr = mysql_real_escape_string($mac_addr);
		
		$sql = "SELECT id FROM networks WHERE coords='$coords' AND mac_addr='$mac_addr'";
		$results = mysql_query($sql) or die(mysql_error());
		
		if(mysql_num_rows($results)>0) 
			return true;
		
		return false;
	}
	
	/**
	* Checks if vendor is in db by mac id
	* @param 
	* @return 
	*/
	function is_vendor_registered($mac_id) 
	{
		$mac_id = mysql_real_escape_string($mac_id);
		
		$sql = "SELECT id FROM vendors WHERE mac_identifier='$mac_id'";
		$results = mysql_query($sql) or die(mysql_error());
		
		if(mysql_num_rows($results)>0) 
			return true;
		
		return false;
	}
	/**
	* Parses object and returns associative array with the information needed to register the AP
	*/
	function parse_ap($obj)
	{
		$mac_addr=return_substr($obj->description,'BSSID: <b>','</b><br/>Capabilities:');
		$vendor=lookup_vendor($mac_addr);
		
		$ap["mac_addr"]=$mac_addr;
		$ap["vendor"]=$vendor;
		$ap["vendor_id"]=get_vendor_id_by_name($vendor);
		$ap["coords"]=$obj->Point->coordinates;
		$ap["ssid"]=$obj->name;
		$ap["frequency"]=return_substr($obj->description,'Frequency: <b>','</b><br/>Level:');
		$ap["encryption"]=return_substr($obj->description,'Capabilities: <b>','</b><br/>Frequency:');
	        if (strlen($ap["encryption"])<3) $ap["encryption"]="none";	
		return $ap;
	}
	
	/**
	* Main function in charge of parsing KML files from the android app "wardrive"
	*/
	function parse_kml($file)
	{
		$ap_list = new SimpleXMLElement($file,null,true);

		foreach($ap_list as $ap) {
			foreach($ap->Folder as $ap_type) {
				debug_log(">$ap_type->name");
				foreach($ap_type->Placemark as $placemark) {
					$ap = parse_ap($placemark);
					debug_ap($ap);
					if(!is_network_registered($ap["coords"], $ap["mac_addr"])) 
					{
						insert_network($ap["coords"], $ap["ssid"], $ap["encryption"], $ap["vendor_id"], $ap["mac_addr"], $ap["frequency"]);
						debug_log("Added new network with SSID:".$ap["ssid"]);
					}
				}
			}
		}
	}

init_db();
$source = $argv[1];
parse_kml($source);

?>
