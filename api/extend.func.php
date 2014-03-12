<?php
defined('IN_DESTOON') or exit('Access Denied');
#Your Functions
function pebug($arr,$exit = true){
  printf("<pre>%s</pre>",var_export($arr,true));
  if($exit) exit();
}
?>
