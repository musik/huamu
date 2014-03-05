<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
//$MOD['site_enable'] or dheader(DT_PATH);
require DT_ROOT.'/include/post.func.php';
$TYPE = get_type('site', 1);
//require MD_ROOT.'/site.class.php';
//$do = new dsite();
$typeid = isset($typeid) ? intval($typeid) : 0;
$seo_title = "云南苗木网";
include template('index');
