<?php
defined('IN_DESTOON') or exit('Access Denied');
function keyword_find_by_letter($letter,$moduleid){
	global $db, $DT_TIME, $DT;
	$r = $db->get_one("SELECT * FROM {$db->pre}keyword WHERE moduleid=$moduleid AND letter='$letter'");
  return $r;
}
?>
