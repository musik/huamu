<?php
$key = 'd8f63kd0';
$status = 3;
define('DT_NONUSER', true);
require '../../common.inc.php';
require DT_ROOT.'/include/post.func.php';
require DT_ROOT.'/include/module.func.php';
if($DT_BOT) dhttp(403);
//$vars = get_defined_vars();
//var_dump(array_keys($vars));
//$vars['GLOBALS'] = false;
//var_export($vars);
require DT_ROOT."/api/post/functions.php";
//mlog($_POST);
$action = $_GET['action'];
switch($action){
  case "new":
    $cl = new AutoPost($_REQUEST['moduleid']);
    $cl->post($_POST);
    break;
  default:
    break;
}
