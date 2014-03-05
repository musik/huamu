<?php
defined('IN_DESTOON') or exit('Access Denied');
$setting = include(DT_ROOT.'/module/'.$module.'/settings/module.php');
update_setting($moduleid, $setting);
$sql = file_get(DT_ROOT.'/module/'.$module.'/settings/schema.sql');
$sql = str_replace('_61', '_'.$moduleid, $sql);
$sql = str_replace('话题', $modulename, $sql);
sql_execute($sql);
include DT_ROOT.'/module/'.$module.'/admin/remkdir.inc.php';
?>
