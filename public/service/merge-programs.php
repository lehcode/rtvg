<?php
$host = '127.0.0.1';
$db_name = 'dev_rutvgid';
$db_user = 'dev_rutvgid';
$db_pass = '127656';
$pfx='rtvg_';
require_once dirname(__FILE__).'/../../../library/Xmltv/String.php';

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
	
	$sql = "SELECT * FROM `".$pfx."programs_props`;";
	
	if(!$result = mysql_query($sql)){
		echo mysql_errno($con).': '.mysql_error($con);
		die(__LINE__);
	}
	
	while($props = mysql_fetch_assoc($result)) {
		
			
		$sql = "SELECT * FROM `".$pfx."programs`
		WHERE `hash`='".$props['hash']."' LIMIT 1;";
		//var_dump($sql);
		
		if(!$program = mysql_query($sql)){
			var_dump(var_dump($props));
			die(__LINE__.': '.mysqlError($con));
		}
		
		$prog = mysql_fetch_assoc($program);
		
		if (count($prog) && $prog!==false){
			$hash = sprintf("%u", crc32( $prog['ch_id'].$prog['start'].$prog['end'] ));
		
			if ((int)$hash==0){
				var_dump($prog);
				die(__FILE__.': '.__LINE__);
			}
		
			$date= ($props['date']!='0000-00-00 00:00:00' && $prog['date']=='0000-00-00 00:00:00' ) ? $props['date'] : 'null' ;
			$premiere = ((bool)$props['premiere']===true) ? 1 : 0 ;
			$length = ((int)$props['length']==0) ? 'null' : "'".$props['length']."'" ;
			$live = ((bool)$props['live']===true && (bool)$prog['live']===false) ? 1 : 0 ;
			$episode_num = ((int)$props['episode_num']>0 && (int)$prog['episode_num']==0) ? (int)$props['episode_num'] : 'null' ;
			$rating = ((int)$prog['rating']>0) ? (int)$prog['rating'] : 'null' ;
		
			$set = array(
					"`hash`=$hash",
					"`country`='".$prog['country']."'",
					"`actors`='".$prog['actors']."'",
					"`directors`='".$prog['directors']."'",
					"`writers`='".$prog['writers']."'",
					"`adapters`='".$prog['adapters']."'",
					"`producers`='".$prog['producers']."'",
					"`composers`='".$prog['composers']."'",
					"`presenters`='".$prog['presenters']."'",
					"`commentators`='".$prog['commentators']."'",
					"`guests`='".$prog['guests']."'",
					"`date`='$date'",
					"`premiere`=$premiere",
					"`length`=$length",
					"`live`=$live",
					"`episode_num`=$episode_num",
					"`rating`=$rating",
			);
			$set = implode(", ", $set);
			
			$sql = "UPDATE `".$pfx."programs` SET $set WHERE `hash`='".$props['hash']."';";
			var_dump($sql);
			mysql_query($sql) or die(__LINE__.': '.mysqlError($con));
				 
			$sql = "DELETE FROM `".$pfx."programs_props` WHERE `hash`='".$props['hash']."';";
			var_dump($sql);
			mysql_query($sql) or die(__LINE__.': '.mysqlError($con));
		 
			$sql = "DELETE FROM `".$pfx."programs_descriptions` WHERE `alias`='".$prog['alias']."';";
			var_dump($sql);
			mysql_query($sql) or die(__LINE__.': '.mysqlError($con));
			
					 
		} else {
		
			$sql = "DELETE FROM `".$pfx."programs_props` WHERE `hash`='".$props['hash']."';";
			var_dump($sql);
			mysql_query($sql) or die(__LINE__.': '.mysqlError($con));
			 
			$sql = "DELETE FROM `".$pfx."programs_descriptions` WHERE ( `alias`='".$prog['alias']."' );";
			var_dump($sql);
			mysql_query($sql) or die(__LINE__.': '.mysqlError($con));
			
		}   
			
	}
	
} while($result);



// Close connection and exit
mysql_close($con);
exit("Готово!");

function mysqlError($con){
	return mysql_errno($con).': '.mysql_error($con);
}