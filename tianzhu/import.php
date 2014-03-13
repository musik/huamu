<?php
include "../common.inc.php";
function comma_split($str){
  return explode(',',$str);
}
$submit = $_REQUEST["submit"];
if($submit){
  $lines = explode("\n",@file_get_contents(DT_ROOT. '/tianzhu/cats.csv'));
  array_pop($lines);
  $data = array_map('comma_split',$lines);
  pr($data);
}
?>
<h1>分类导入</h1>
  <form method="get">
<label for="moduleid">导入</label>
<select name="moduleid" id="moduleid">
<option value="0">请选择</option>
<?php
foreach($MODULE as $m) {
	if($m['moduleid'] < 4 || $m['moduleid'] == $mid || $m['islink']) continue;
	echo '<option value="'.$m['moduleid'].'">'.$m['name'].'</option>';
}
?>
</select>
<br />
<label for="keep"> 当前模块分类数据</label>
<input type="radio" name="keep" value="1" checked/> 保留
<input type="radio" name="keep" value="0"/> 删除
<br />
<input type="submit" name="submit" value="导入" />
</form>
<?php
