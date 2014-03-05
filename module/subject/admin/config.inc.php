<?php
defined('IN_DESTOON') or exit('Access Denied');
$MCFG['module'] = 'subject';
$MCFG['name'] = '主题';
$MCFG['author'] = 'ynlp.com';
$MCFG['homepage'] = 'www.ynlp.com';
$MCFG['copy'] = true;
$MCFG['uninstall'] = true;

$RT = array();
$RT['file']['index'] = '主题管理';
$RT['file']['html'] = '更新网页';

$RT['action']['index']['add'] = '添加主题';
$RT['action']['index']['edit'] = '修改主题';
$RT['action']['index']['delete'] = '删除主题';
$RT['action']['index']['check'] = '审核主题';
$RT['action']['index']['expire'] = '过期主题';
$RT['action']['index']['reject'] = '未通过主题';
$RT['action']['index']['recycle'] = '回收站';
$RT['action']['index']['move'] = '移动主题';
$RT['action']['index']['level'] = '主题级别';

$CT = true;
?>
