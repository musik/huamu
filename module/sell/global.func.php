<?php
defined('IN_DESTOON') or exit('Access Denied');
function keyword_find_by_letter($letter,$moduleid){
	global $db, $DT_TIME, $DT;
	//echo("SELECT * FROM {$db->pre}keyword WHERE moduleid=$moduleid AND letter='$letter'");
	$r = $db->get_one("SELECT * FROM {$db->pre}keyword WHERE moduleid=$moduleid AND letter='$letter'");
  return $r;
}
function parse_keyword_content($text){
  $text= str_replace("```",',',$text);
  $text= str_replace("'",'"',$text);
  $text = preg_replace("/(url|photo|title|website|company)\:/",'"$1":',$text);
  $text = "[$text]";
  $arr = json_decode($text,true);
  return $arr;
}
?>
