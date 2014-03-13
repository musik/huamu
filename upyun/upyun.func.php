<?php
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT. '/upyun/upyun.class.php';
/**
 * Returns the size of a file without downloading it, or -1 if the file
 * size could not be determined.
 *
 * @param $url - The location of the remote file to download. Cannot
 * be null or empty.
 *
 * @return The size of the file referenced by $url, or -1 if the size
 * could not be determined.
 */
function curl_get_file_size( $url ) {
  // Assume failure.
  $result = -1;
  $curl = curl_init( $url );

  // Issue a HEAD request and follow any redirects.
  curl_setopt( $curl, CURLOPT_NOBODY, true );
  curl_setopt( $curl, CURLOPT_HEADER, true );
  curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
  //curl_setopt( $curl, CURLOPT_USERAGENT, get_user_agent_string() );

  $data = curl_exec( $curl );
  curl_close( $curl );

  if( $data ) {
    $content_length = "unknown";
    $status = "unknown";

    if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
      $status = (int)$matches[1];
    }

    if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
      $content_length = (int)$matches[1];
    }

    // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
    if( $status == 200 || ($status > 300 && $status <= 308) ) {
      $result = $content_length;
    }
  }

  return $result;
}
function upyun_client($type = 'image'){
  global $CFG;
  //pr($CFG["upyun_".$type."_bucket"]);
  return new UpYun($CFG["upyun_".$type."_bucket"],$CFG['upyun_username'],$CFG['upyun_password'],UpYun::ED_TELECOM);
}
function upyun_upload($file,$filename,$type='image',$opts=array()){
  //pr(func_get_args());
  try{
    $upyun = upyun_client($type);
    $fh = fopen($file,'rb');
    $handler = (substr($file,0,7) == 'http://') ? stream_get_contents($fh) : $fh;
    $upyun->writeFile( $filename,$handler,true,$opts);
    fclose($fh);
    return true;
  }catch(Exception $e){
    //pr($upyun);
    //pr($e);
    return ($e);
  }
}
function upyun_rm_r($dir,$type='image'){
  $upyun = upyun_client($type);
  $list = $upyun->getList($dir);
  foreach($list as $file){
    if($file['type'] == 'file'){
      echo $dir . $file['name'];
      $upyun->delete($dir . $file['name']);
    }else{
      upyun_rm_r($dir . $file['name'] . '/',$type);
    }
  }
  $upyun->delete($dir);
}
function upyun_file_exists($file,$type='image'){
  try{
    $upyun = upyun_client($type);
    return $upyun->getFileInfo($file);
  }catch(Exception $e){
    return false;
  }
}
class upyun_uploader {
  var $savename;
  var $file_error;
  var $overwrite = false;
  var $errmsg = errmsg;
  var $uptime = 0;
  var $adduserid = true;
  function __construct($_files,$savepath,$savename='',$fileformat=''){
    global $DT, $_userid;
    if(is_string($_files)){
      $this->remote = 1;
      $this->file = $_files;
      $this->file_name = $_files;
      $this->ext = file_ext($this->file_name);
      in_array($this->ext, array('jpg', 'jpeg', 'gif', 'png', 'bmp')) or $this->ext = 'jpg';
      $this->file_size = curl_get_file_size($this->file);
      $this->image = 1;
    }else{
      $this->remote = 0;
      $file = array_shift($_files);
      $this->file = $file['tmp_name'];
      $this->file_name = $file['name'];
      $this->file_size = $file['size'];
      $this->file_type = $file['type'];
      $this->file_error = $file['error'];
      $this->ext = file_ext($this->file_name);
      $this->savename = empty($savename) ? $this->file_name  : $savename;
      $this->image = $this->is_image();
    }
    $this->userid = $_userid;
    $this->fileformat = $fileformat ? $fileformat : $DT['uploadtype'];
    $this->maxsize = $DT['uploadsize'] ? $DT['uploadsize']*1024 : 2048*1024;
    $this->savepath = $savepath;
    $this->opts = array();
    $this->bucket_type = $this->image ? "image" : "file";
    //$this->saveto = "/" . $this->savepath .. $this->savename;
  }
  function is_bad_image(){
    return ($this->image && !@getimagesize($this->file)) ? true : false;
  }
  function is_allow() {
    if($this->remote){
      if(strlen($this->file) < 18 || strpos($this->file, '://') === false) return false;
    }else{
      if(!$this->fileformat) return false;
      if(!preg_match("/^(".$this->fileformat.")$/i", $this->ext)) return false;
      if(preg_match("/^(php|phtml|php3|php4|jsp|exe|dll|cer|shtml|shtm|asp|asa|aspx|asax|ashx|cgi|fcgi|pl)$/i", $this->ext)) return false;
    }
    return true;
  }
  function url(){
   global $CFG;  
   return "http://".$CFG["upyun_".$this->bucket_type."_bucket"] . $CFG["upyun_host"] . $this->saveto;
  }

  function save(){
    include load('include.lang');
    //upload error
    if($this->file_error == UPLOAD_ERR_PARTIAL || $this->file_error == UPLOAD_ERR_NO_FILE || $this->file_error == UPLOAD_ERR_INI_SIZE || $this->file_error == UPLOAD_ERR_FORM_SIZE) return $this->_($L['upload_failed'].' ('.$this->file_error.')');
    //file large
    if($this->maxsize > 0 && $this->file_size > $this->maxsize) return $this->_($L['upload_size_limit'].' ('.intval($this->maxsize/1024).'Kb)');
    if(!$this->is_allow()) return $this->_($L['upload_not_allow']);
    
    $this->set_savepath($this->savepath);
    $this->set_savename($this->savename);
    $status = upyun_upload($this->file,$this->saveto,$this->bucket_type,$this->opts);
    if($status === true) return true;
    return $this->_($status->getMessage());
  }
  function set_size($type,$value,$quality=95,$unsharp=true){
    $this->opts = array(
      'x-gmkerl-type' => $type,
      'x-gmkerl-value' => $value,
      'x-gmkerl-quality' => $quality,
      'x-gmkerl-unsharp' => $unsharp
    );
    $this->bucket_type = "nothumb";
  }
  function set_savepath($savepath) {
    $savepath = str_replace("\\", "/", $savepath);
    $savepath = substr($savepath, -1) == "/" ? $savepath : $savepath."/";
    $savepath = substr($savepath, 0,1) == "/" ? $savepath : "/".$savepath;
    $this->savepath = $savepath;
  }
  function set_savename($savename) {
    global $DT_TIME;
    $this->uptime = $DT_TIME;
    if($savename) {
      $this->savename = $this->adduserid ? str_replace('.'.$this->ext, $this->userid.'.'.$this->ext, $savename) : $savename;
    } else {
      $name = date('H-i-s', $this->uptime).'-'.rand(10, 99);
      $this->savename = $this->adduserid ? $name.'-'.$this->userid.'.'.$this->ext : $name.'.'.$this->ext;
    }
    $this->saveto = $this->savepath.$this->savename;		
    if(!$this->overwrite && $this->file_exist($this->saveto)) {
      $i = 1;
      while($i) {
        $saveto = str_replace('.'.$this->ext, '-'.$i.'.'.$this->ext, $this->saveto);
        if($this->file_exist($saveto)) {
          $i++;
          continue; 
        } else {
          $this->saveto = $saveto; 
          break;
        }
      }
    }
    $this->url = $this->url();
  }
  function is_image() {
      return preg_match("/^(jpg|jpeg|gif|png|bmp)$/i", $this->ext);
  }
  function file_exist($file){
    return upyun_file_exists($file,$this->bucket_type);
  }
  function _($e) {
    $this->errmsg = $e;
    return false;
  }


}

?>
