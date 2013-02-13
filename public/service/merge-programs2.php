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
set_time_limit(0);

/*
 * Process
 */
do {
	
    
	$sql = "SELECT `hash` FROM `".$pfx."programs` WHERE LENGTH(`hash`)=32";
	var_dump($sql);
	if(!$r = mysql_query($sql)){
		echo mysql_errno($con).': '.mysql_error($con);
		die(__LINE__);
	}
	
	while($prog = mysql_fetch_assoc($r)) {
					
	    $sql = "SELECT * FROM `".$pfx."programs` WHERE `hash`='".$prog['hash']."' LIMIT 1";
		var_dump($sql);
		if(!$r2 = mysql_query($sql)){
			die(__LINE__.': '.mysqlError($con));
		}
		$oldProg = mysql_fetch_assoc($r2);
		
		//var_dump($oldProg);
		//die(__FILE__.': '.__LINE__);
		
		if ((bool)$prog!==false){
			
		    $newHash = sprintf("%u", crc32( $oldProg['ch_id'].$oldProg['start'].$oldProg['end'] ));
		    $sql = "SELECT * FROM `".$pfx."programs_props` WHERE `hash`='$newHash'";
		    var_dump($sql);
		    if(!$pr = mysql_query($sql)){
		    	die(__LINE__.': '.mysqlError($con));
		    }
		    $newProps = mysql_fetch_assoc($pr);
		    
		    if (isset($newProps['alias'])){
		        if ($newProps['alias'] == $oldProg['alias']){
			        fixProgram($con, $pfx, $newHash);
			    } else {
			        deleteProps($pfx, $newHash, $oldProg['alias']);
			    }
		    }
		    
		    updateProgram($con, $pfx, $oldProg['hash'], $newHash);
		    deleteProgram($con, $pfx, $oldProg['hash']);
		    
		    
		} else {
		    die(__FILE__.': '.__LINE__);
			
		}
			
	}
	
} while($r);



// Close connection and exit
mysql_close($con);
exit("Готово!");

function fixProgram($con, $pfx, $new_hash){
	
    $sql = "SELECT * FROM `".$pfx."programs` WHERE `hash`='$new_hash' LIMIT 1";
    var_dump($sql);
    if(!$result = mysql_query($sql)){
    	die(__LINE__.': '.mysqlError($con));
    }
    $prog = mysql_fetch_assoc($result);
    
    var_dump($prog);
    
    $sql = "SELECT * FROM `".$pfx."programs_props` WHERE `alias`='".$prog['alias']."' LIMIT 1";
    var_dump($sql);
    if(!$result = mysql_query($sql)){
    	die(__LINE__.': '.mysqlError($con));
    }
    $props = mysql_fetch_assoc($result);
    
    var_dump($props);
    die(__FILE__.': '.__LINE__);
    
    $sql = "UPDATE `".$pfx."programs` SET
    `live`='$new_hash',
    WHERE `hash`='".$prog['hash']."'";
    var_dump($sql);
    mysql_query($sql) or die(__LINE__.': '.mysqlError($con));
    
}

function deleteProps($pfx, $new_hash, $alias){
    
    $sql = "DELETE FROM `".$pfx."programs_props` WHERE `hash`='$new_hash'";
    //var_dump($sql);
    mysql_query($sql);
    
    $sql = "DELETE FROM `".$pfx."programs_props` WHERE `alias`='$alias'";
    //var_dump($sql);
    mysql_query($sql);
    
}

function updateProgram($con=null, $pfx=null, $old_hash=null, $new_hash=null){
    
    $sql = "UPDATE `".$pfx."programs` SET `hash`='$new_hash' WHERE `hash`='$old_hash'";
    //var_dump($sql);
    if (!mysql_query($sql)){
    	die(__FUNCTION__.': '.mysqlError($con));
    }
    return true;
    
}

function deleteProgram($con=null, $pfx=null, $old_hash=null){
    
    $sql = "DELETE FROM `".$pfx."programs` WHERE `hash` = '$old_hash'";
	//var_dump($sql);
    if (!mysql_query($sql)){
    	die(__FUNCTION__.': '.mysqlError($con));
    }
    return true;
    
}

function mysqlError($con){
	return mysql_errno($con).': '.mysql_error($con);
}