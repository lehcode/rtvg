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

do {
    
	$sql = "SELECT `hash`,`alias`,`date` FROM `".$pfx."programs`";
	if(!$r = mysql_query($sql)){
		echo mysql_errno($con).': '.mysql_error($con);
		die(__FILE__.': '.__LINE__);
	}
	
	while($prog = mysql_fetch_assoc($r)) {
	    
	    var_dump($prog);
	    
	    $sql = "SELECT * FROM `".$pfx."programs_props` WHERE `alias`='".$prog['alias']."'";
	    
	    if(!$r2 = mysql_query($sql)){
	    	echo mysql_errno($con).': '.mysql_error($con);
	    	die(__FILE__.': '.__LINE__);
	    }
	    $props = mysql_fetch_assoc($r2);
	    
	    var_dump($props);
	    
	    if ($props){
	        
	        $set = array();
	        
	        if ($props['date']!='0000-00-00 00:00:00') {
	            $date = $props['date'];
	        } else {
	            if ($prog['date']!='0000-00-00 00:00:00') {
	            	$date = $prog['date'];
	            } else {
	                $date = "'NULL'";
	            }
	        }
	        $date = $date=='' ? "'NULL'" : $date ;
	        
	        $set[] = "`date`=".$date;
	        $set[] = "`country`='".$props['country']."'";
	        $set[] = "`actors`='".$props['actors']."'";
	        $set[] = "`directors`='".$props['directors']."'";
	        $set[] = "`writers`='".$props['writers']."'";
	        $set[] = "`adapters`='".$props['adapters']."'";
	        $set[] = "`producers`='".$props['producers']."'";
	        $set[] = "`composers`='".$props['composers']."'";
	        $set[] = "`editors`='".$props['editors']."'";
	        $set[] = "`presenters`='".$props['presenters']."'";
	        $set[] = "`commentators`='".$props['commentators']."'";
	        $set[] = "`guests`='".$props['guests']."'";
	        $episode = (int)$props['episode_num']>0 ? (int)$props['episode_num'] : "'NULL'" ;
	        $set[] = "`episode_num`=$episode";
	        $live = (int)$props['live']==1 ? 1 : 0 ;
	        $set[] = "`live`=$live";
	        $length = $props['length']=='00:00' || (int)$props['length']==0 ? "'NULL'" : (int)$props['length'] ;
	        $set[] = "`length`=$length";
	        $premiere = (int)$props['premiere']==1 ? 1 : 0 ;
	        $set[] = "`premiere`=$premiere";
	        
	        $sql = "UPDATE `".$pfx."programs` SET ".implode(', ', $set)." WHERE `hash`=".$prog['hash'];
	        var_dump($sql);
	        mysql_query($sql) or die(__LINE__.mysql_error($con));
	            
	        $sql = "DELETE FROM `".$pfx."programs_props` WHERE `alias`='".$prog['alias']."'";
	        var_dump($sql);
	        mysql_query($sql) or die(__LINE__.mysql_error($con));
	        
	    	//die(__FILE__.': '.__LINE__);
	    	
	    }
	    
	    /*
	    $sql = "SELECT COUNT(*) FROM `".$pfx."programs_descriptions` WHERE `alias`='".$prog['alias']."';";
	    var_dump($sql);
	    if(!$r2 = mysql_query($sql)){
	    	echo mysql_errno($con).': '.mysql_error($con);
	    	die(__FILE__.': '.__LINE__);
	    }
	    $desc = mysql_fetch_assoc($r2);
	    
	    if((int)$desc['COUNT(*)']>1){
	        //var_dump((int)$desc['COUNT(*)']);
	        
	        $sql = "DELETE FROM `".$pfx."programs_descriptions` WHERE `alias`='".$prog['alias']."';";
            var_dump($sql);
	        if(!$r3 = mysql_query($sql)){
	        	echo mysql_errno($con).': '.mysql_error($con);
	        	die(__LINE__);
	        }
	        
	        //die(__FILE__.': '.__LINE__);
	        
	        
	    }
	    */
	    //die(__LINE__);
	    
	}
    
} while($r);