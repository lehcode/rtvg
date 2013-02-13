<?php
$host = '127.0.0.1';
$db_name = 'dev_rutvgid';
$db_user = 'dev_rutvgid';
$db_pass = '127656';
$pfx='rtvg_';
require_once dirname(__FILE__).'/../../library/Xmltv/String.php';

if (!$con = mysql_connect($host, $db_user, $db_pass)){
	die("Cannot connect to database: ".mysql_error());
}
mysql_select_db($db_name, $con);
set_time_limit(0);

do {
    
	$sql = "SELECT COUNT( * ) AS `amt` , `hash`, `alias`
	FROM `rtvg_programs_descriptions`
	GROUP BY `alias`
	ORDER BY `amt` DESC";
	
	if(!$r = mysql_query($sql)){
		echo mysql_errno($con).': '.mysql_error($con);
		die(__FILE__.': '.__LINE__);
	}
	
	while($prog = mysql_fetch_assoc($r)) {
	    
	    var_dump($prog);
	    
	    if ((int)$prog['alias']!=1){
	        
	        $sql = "SELECT COUNT(*) FROM `".$pfx."programs_descriptions` WHERE `alias`='".$prog['alias']."'";
	        var_dump($sql);
	        if(!$cnt = mysql_query($sql)){
	        	echo mysql_errno($con).': '.mysql_error($con);
	        	die(__FILE__.': '.__LINE__);
	        }
	        $count = mysql_fetch_assoc($cnt);
	        $count = (int)$count['COUNT(*)'];
	        
	        if ($count>1){
	            //die(__FILE__.': '.__LINE__);
	        	do {
	            	//var_dump( $count );
	                //die(__FILE__.': '.__LINE__);
	                $delete = "DELETE FROM `".$pfx."programs_descriptions` WHERE ( `alias`='".$prog['alias']."' ) LIMIT 1";
	                var_dump( $delete );
	                if (!mysql_query( $delete )){
	                    echo mysql_errno($con).': '.mysql_error($con);
	                    die(__FILE__.': '.__LINE__);
	                }
	                
	                $deleted = mysql_affected_rows($con);
	                if ((int)$deleted==0){
	                    var_dump( $count );
	                    //var_dump( $delete );
	                    var_dump( $deleted );
	                    echo mysql_errno($con).': '.mysql_error($con);
	                    die(__FILE__.': '.__LINE__);
	                }
	                
	                $count-=1;
	                
	            } while($count>1); 
	        } else {
	                var_dump($prog);
	                die(__FILE__.': '.__LINE__);
	            }
	    }
	    
	    //die(__FILE__.': '.__LINE__);
	    
	}
    
} while($r);