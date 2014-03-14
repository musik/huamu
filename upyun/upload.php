<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2011 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
//error js
$js = $errjs = '';
if($from == 'thumb' || $from == 'album' || $from == 'photo' || $from == 'file') {
	$errjs .= 'window.parent.cDialog();';
} else if($from == 'editor' || $from == 'attach') {
	$errjs .= 'window.parent.GetE("frmUpload").reset();';
}
//upload limit
$session = new dsession();
$limit = intval($MG['uploadlimit']);
$total = isset($_SESSION['uploads']) ? count($_SESSION['uploads']) : 0;
if($limit && $total > $limit - 1) {
  $errmsg = 'Error(5)'.lang('message->upload_limit', array($limit));
  if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
  dalert($errmsg, '', $errjs);
  exit();
}
//
require DT_ROOT.'/upyun/config.inc.php';
require DT_ROOT.'/upyun/upyun.func.php';
$uploaddir = '/'.timetodate($DT_TIME, $DT['uploaddir']).'/';
if($MG['uploadtype']) $DT['uploadtype'] = $MG['uploadtype'];
if($MG['uploadsize']) $DT['uploadsize'] = $MG['uploadsize'];
if($remote && strlen($remote) > 17 && strpos($remote, '://') !== false) {
  $_files = $remote;
} else {
  $_files = $_FILES;
}
$do = new upyun_uploader($_files,$uploaddir);
if($do->is_bad_image()){
  $errmsg = 'Error(6)'.lang('message->upload_bad');
  if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
  dalert($errmsg, '', $errjs);
  exit();
}
if($do->image){
  if($from == 'thumb'){
    if($width && $height) {
      $do->set_size("fix_both",$width."x".$height);
    }
  }
}
error_reporting(E_ALL);
if($do->save()) {
  $img_w = $img_h = 0;
	$saveto = $do->url;
  if($do->image){
    $info = getimagesize($saveto);
    $img_w = $info[0];
    $img_h = $info[1];
    $do->filesize = curl_get_file_size($saveto);
    if(in_array($from,array('album','photo'))){
      $saveto .= "!thumb";
    }
  }

	$fid = isset($fid) ? $fid : '';
	if(!$DT["upyun"] && isset($old) && $old && in_array($from, array('thumb', 'photo'))) delete_upload($old, $_userid);
	$_SESSION['uploads'][] = $swfupload ? str_replace('.thumb.'.$do->ext, '', $saveto) : $saveto;
	if($DT['uploadlog']) $db->query("INSERT INTO {$upload_table} (item,fileurl,filesize,fileext,upfrom,width,height,moduleid,username,ip,addtime,itemid) VALUES ('".md5($saveto)."','$saveto','$do->file_size','$do->ext','$from','$img_w','$img_h','$moduleid','$_username','$DT_IP','$do->uptime','$itemid')");
	if($swfupload) exit('FILEID:'.$saveto);
	$pr = 'parent.document.getElementById';
	if($from == 'thumb') {
		$js .= 'try{'.$pr.'("d'.$fid.'").src="'.$saveto.'";}catch(e){}';
		$js .= $pr.'("'.$fid.'").value="'.$saveto.'";';
		$js .= 'window.parent.cDialog();';
	} else if($from == 'album' || $from == 'photo') {
		$js .= 'window.parent.getAlbum("'.$saveto.'", "'.$fid.'");';
		$js .= $from == 'photo' ? $pr.'("dform").submit();' : 'window.parent.cDialog();';
	} else if($from == 'editor') {
		$js .= 'window.parent.SetUrl("'.$saveto.'");';
		$js .= 'window.parent.GetE("frmUpload").reset();';
	} else if($from == 'attach') {
		$js .= 'window.parent.GetE("txtUrl").value="'.$saveto.'";';
		$js .= 'window.parent.GetE("frmUpload").reset();';
	} else if($from == 'file') {
		if($moduleid == 2 && $fid == 'chat') {
			$js .= $pr.'("word").value="'.$saveto.'";';
			$js .= 'window.parent.chat_send();';
		} else {
			$js .= $pr.'("'.$fid.'").value="'.$saveto.'";';
			if($module == 'down') $js .= 'window.parent.initd('.dround($do->file_size/1024/1024, 2).');';
		}
		$js .= 'window.parent.cDialog();';
	}
	dalert('', '', $js);
} else {
	$errmsg = 'Error(8)'.$do->errmsg;
	if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
	dalert($errmsg, '', $errjs);
}
?>
