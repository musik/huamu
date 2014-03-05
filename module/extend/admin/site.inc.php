<?php
defined('IN_DESTOON') or exit('Access Denied');
$TYPE = get_type('site', 1);
//require MD_ROOT.'/link.class.php';
//$do = new dlink();
$menus = array (
  array('添加分站', '?moduleid='.$moduleid.'&file='.$file.'&action=add'),
  array('分站列表', '?moduleid='.$moduleid.'&file='.$file),
  array('审核分站', '?moduleid='.$moduleid.'&file='.$file.'&action=check'),
  array('分站分类', 'javascript:Dwidget(\'?file=type&item='.$file.'\', \'分站分类\');'),
  array('模块设置', '?moduleid='.$moduleid.'&file=setting#'.$file),
);
switch($action) {
default:
  include tpl('site', $module);
  break;
}
