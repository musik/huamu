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
mlog($_POST,'');
$action = $_GET['action'];
$moduleid = $_REQUEST['moduleid'];
$test = $_GET['test'];
switch($action){
  case "new":
    $ap = new AutoPost($moduleid);
    if($test){
      include DT_ROOT.'/api/post/test/'. $moduleid.'.php';
    }
    if(!$post)
      $post = $_POST;
    $ap->post($post);
    break;
  case "cats":
    $cats = get_maincat(0,$moduleid);
    foreach($cats as $cat){
      echo "$cat[catid]|$cat[catname]","<br />";
    }
    break;
  default:
    break;
}
