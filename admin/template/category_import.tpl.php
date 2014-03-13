<?php
defined('IN_DESTOON') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
<form method="post" action="?" onsubmit="return check();">
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<input type="hidden" name="action" value="<?php echo $action;?>"/>
<input type="hidden" name="mid" value="<?php echo $mid;?>"/>
<div class="tt">分类导入</div>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td class="tl"><span class="f_red">*</span> 当前模块分类数据</td>
<td>
<input type="radio" name="save" value="1" checked/> 保留
<input type="radio" name="save" value="0"/> 删除
</td>
</tr>
</table>
<div class="sbt"><input type="submit" name="submit" value="复 制" class="btn"/></div>
</form>
<script type="text/javascript">
function check() {
	if(Dd('toid').value==0) {
		Dmsg('请选择来源模块', 'toid');
		return false;
	}
	return confirm('此操作不可撤销，确定要执行吗？');
}
</script>
<script type="text/javascript">Menuon(3);</script>
<?php include tpl('footer');?>
