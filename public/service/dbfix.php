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

$limit  = 100;
var_dump($limit);
$offset = 0;
do {
    
    $sql = "SELECT * FROM `".$pfx."programs_props` ORDER BY `id` ASC LIMIT $offset,$limit ";
    if(!$result = mysql_query($sql)){
    	die(mysqlError($con));
    }
    $offset += $limit;
    
    var_dump($offset);
    die(__FILE__.': '.__LINE__);
     
} while($result);

// Close connection and exit
mysql_close($con);
exit("Готово!");


/**
 * Cleanp wrong names
 * @var string
 */
/*
$sql = "SELECT * FROM `".$pfx."actors` ORDER BY `id` ASC";
if(!$result = mysql_query($sql)){
	die(mysqlError($con));
}
$namesRegex = array(
	'/^(\p{Lu}\p{Ll})-(\p{Lu}\p{Ll}?)\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll})\.(\p{Lu})\.\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu})\s(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)$/u',
	'/^([a-zA-Z]+)\s((Mac|Mc)?[a-zA-Z]+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s((Мак)?\p{Lu}\p{Ll}+)\s(\p{Lu}+)$/u',
	'/^((О|Ти|Де|Ди|Мак|Ар)`?\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)-(\p{Lu}\p{Ll}+)-(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s"(\p{Lu}\p{Ll}+)"\s(\p{Lu}\p{Ll}+)(-мл)$/u',
	'/^(\p{Lu}\p{Ll}+)\s"(\p{Lu}\p{Ll}+)"\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu})\.\s(\p{Lu})\.\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll})\.\s((О)(`)\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(Мак\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll})\.\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu})\.(\p{Lu})\.\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu})\.(\p{Lu})\.\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll})\.\s(\p{Lu}\p{Ll}+)-(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu})\.(\p{Lu}\p{Ll})\.\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll})\.\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s((Ле|Ла|Ди|Дю)\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)\s(\p{Lu}`(\p{Lu}|\p{Ll})\p{Ll}+)$/ui',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}`\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)$/ui',
	'/^(\p{Lu}\p{Ll}+)\s((О\s|Мак|О`|О’|Делл)?\p{Lu}\p{Ll}+)$/ui',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)\.\s(\p{Lu}\p{Ll}+)$/ui',
	'/^(\p{Lu}\p{Ll}+)\.\s(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)$/ui',
	'/^(\p{Lu}\p{Ll}+)\s((О|Д|О)(`|"|\')\s*\p{Lu}\p{Ll}+)$/ui',
	'/^(\p{Lu}\p{Ll}+)\s\p{Lu}\.\s(\p{Lu}\p{Ll}+)$/ui',
	'/^(\p{Lu}\p{Ll}+)-((\p{Lu}|\p{Ll})\p{Ll}+)\s(\p{Lu}\p{Ll}+)$/ui',
	'/^(\p{Lu}\p{Ll}+)-(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)\s(мл\.|ст\.|Мл\.|Ст\.|мл|ст)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)-(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+|фон|Фон|де|Де|ди|Ди|дас|ла|ван|Ван|ле|дю)\s(\p{Lu}\p{Ll}+)-(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+|фон|Фон|де|Де|ди|Ди|дас|ла|ван|Ван|ле|дю|ЛаРю)\s(\p{Lu}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}{1,2}\p{Ll}+)$/u',
	'/^(\p{Lu}\p{Ll}+)\s(\p{Lu})$/u',
);
$delete = array();
$ok = array();
while($row = mysql_fetch_assoc($result)) {
	var_dump($row['complete_name']);
	
	if(preg_match('/^(\p{Lu})\.\s/ui', $row['complete_name'], $m)){
		$sql = "DELETE FROM `rtvg_actors` WHERE `id` = '".$row['id']."'";
		if(!$result = mysql_query($sql)){
			die(mysqlError($con));
		}
	}
	
	foreach ($namesRegex as $r){
		$match=false;
		if (preg_match($r, $row['complete_name'], $m)) {
			$match=true;
			var_dump($r);
			$ok[]=$row['complete_name'];
			break;
		}
	}
	
	if (!$match){
		if (!in_array((int)$row['id'], $delete)) {
			$sql = "DELETE FROM `rtvg_actors` WHERE `id` = '".$row['id']."'";
			if(!$result = mysql_query($sql)){
				die(mysqlError($con));
			}
		}
	}
}
*/

/**
 * Fix icon filenames to PNG
 */
/*
$sql = "SELECT * FROM `".$pfx."channels`";
var_dump($sql);
if(!$result = mysql_query($sql)){
	die(mysqlError($con));
}
while($row = mysql_fetch_assoc($result)) {
	var_dump($row['icon']);
	$newIcon = Xmltv_String::str_ireplace('default-icon', 'default', $row['icon']);
	$sql = "UPDATE `".$pfx."channels` SET `icon`='$newIcon' WHERE `ch_id`='".(int)$row['ch_id']."'";
	if (!mysql_query($sql)){
		die(mysqlError($con));
	}
}
*/

/**
 * Find duplicate actors
 * @var string
 */
/* 
$sql = "SELECT `complete_name`, COUNT(*) c FROM `".$pfx."actors` GROUP BY `complete_name` HAVING c>1";
var_dump($sql);
if(!$result = mysql_query($sql)){
	die(mysqlError($con));
}
//$i=0;
$r = array();
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
	$sql = "SELECT `id`,`complete_name` FROM `rtvg_actors` WHERE `complete_name`='".$row[0]."' LIMIT 0, ".$row[1]." ";
	if(!$dupes = mysql_query($sql)){
		die(mysqlError($con));
	}
	//var_dump($sql);
	$i=0;
	while($actor = mysql_fetch_array($dupes, MYSQL_NUM)) {
		if ($i>0){
			$delete[] = (int)$actor[0];
		}
		$i++;
	}
	//var_dump($delete);
	//die();
}
//var_dump($delete);
$sql = "DELETE FROM `rtvg_actors` WHERE `id` IN (".implode(',', $delete).")";
echo($sql);
 */

/**
 * Find duplicate directors
 * @var string
 */
/*
$sql = "SELECT `complete_name`, COUNT(*) c FROM `rtvg_directors` GROUP BY `complete_name` HAVING c>1";
var_dump($sql);
$result = mysql_query($sql);
//$i=0;
$r = array();
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
	$sql = "SELECT `id`,`complete_name` FROM `rtvg_directors` WHERE `complete_name`='".$row[0]."' LIMIT 0, ".$row[1]." ";
	$dupes = mysql_query($sql);
	//var_dump($sql);
	$i=0;
	while($actor = mysql_fetch_array($dupes, MYSQL_NUM)) {
		if ($i>0){
			$delete[] = (int)$actor[0];
		}
		$i++;
	}
	//var_dump($delete);
	//die();
}
//var_dump($delete);
$sql = "DELETE FROM `rtvg_directors` WHERE `id` IN (".implode(',', $delete).")";
echo($sql);

die();
*/


/*
 * //Генерация полных имен актеров и режиссеров
$sql = "SELECT `id`, CONCAT(`f_name`,' ',`m_name`,' ',`s_name`,' ',`rank`) FROM `rtvg_directors`
ORDER BY `id` ASC";
var_dump($sql);

$result = mysql_query($sql);
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
	//var_dump($row);
	$cn = trim( Xmltv_String::str_ireplace('  ', ' ', $row[1]) );
	//var_dump($cn);
	$sql = "UPDATE `rtvg_directors` SET `complete_name`='$cn' WHERE `id`='".(int)$row[0]."'";
	mysql_query($sql);
}
*/





function mysqlError($con){
	return mysql_errno($con).': '.mysql_error($con);
}