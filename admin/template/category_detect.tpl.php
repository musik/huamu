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
<td class="tl"><span class="f_hid">*</span> 上级分类</td>
<td><?php echo category_select('parentid', '请选择', $parentid, $mid);?><?php tips('如果不选择，则为顶级分类');?></td>
<td>
</td>
</tr>
</table>
<div class="sbt"><input type="submit" name="submit" value="分类" class="btn"/></div>
</form>
<script type="text/javascript">
function check() {
	//return confirm('此操作不可撤销，确定要执行吗？');
}
</script>
<script type="text/javascript">Menuon(4);</script>
<?php include tpl('footer');?>
