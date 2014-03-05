<?php
defined('IN_DESTOON') or exit('Access Denied');
function extend_get_fields(){
  global $FD,$DT_PRE,$table;
  if(!isset($FD))
    $FD = cache_read('fields-'.substr($table, strlen($DT_PRE)).'.php');
  return $FD;
}
?>
