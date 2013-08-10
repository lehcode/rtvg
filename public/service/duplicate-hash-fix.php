<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 30711);

$host = '127.0.0.1';
$db_name = 'dev_rutvgid';
$db_user = 'dev_rutvgid';
$db_pass = '127656';
$pfx='rtvg_';

if (!$con = mysql_connect($host, $db_user, $db_pass)){
	die("Cannot connect to database: ".mysql_error());
}
mysql_select_db($db_name, $con);

$limit  = 1000;
//var_dump($limit);
$offset = 0;
//var_dump($offset);
set_time_limit(0);

do {
	
	$sql = "SELECT COUNT( * ) AS `Count` , `hash` 
	FROM `rtvg_programs`
	GROUP BY `hash`
	ORDER BY `Count` DESC";
	
	if(!$result = mysql_query($sql)){
		echo mysql_errno($con).': '.mysql_error($con);
		die(__FILE__.': '.__LINE__);
	}
	
	$dupes = array();
	while($row = mysql_fetch_assoc($result)) {
		
		//var_dump($row);
		
		if ((int)$row['Count']>1){
			$dupes[] = "'".$row['hash']."'";
		}
		
		//die(__FILE__.': '.__LINE__);
		
	}
	
	if (count($dupes)){
		//var_dump($dupes);
		$sql = "DELETE FROM `rtvg_programs` WHERE `hash` IN (".implode(', ',$dupes).")";
		//var_dump($sql).PHP_EOL;
		//die(__FILE__.': '.__LINE__);
		if(!mysql_query($sql)){
			echo mysql_errno($con).': '.mysql_error($con);
			die(__FILE__.': '.__LINE__);
		}
	}
	
} while($result);



// Close connection and exit
mysql_close($con);
exit("Готово!");

function mysqlError($con){
	return mysql_errno($con).': '.mysql_error($con);
}