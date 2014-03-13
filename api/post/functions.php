<?php

function mlog($string){
  if(!is_string($string))
    $string = var_export($string,true);
  $filename = DT_ROOT."/api/post/tmp.txt";
  file_put_contents($filename,$string,true);
}
class AutoPost{
  function __construct($moduleid){
    $this->moduleid = $moduleid;
    $this->mod = cache_read("module-".$moduleid.'.php');
    $this->module = $this->mod['module'];
  }
  function post($data){
    require DT_ROOT . "/module/{$this->module}/{$this->module}.class.php";
    $data = $this->parse_data($data);
    $cl = new $this->module($this->moduleid); 
    $cl->table = get_table($this->moduleid);
    $cl->table_data = get_table($this->moduleid,1);
    $cl->table_search = str_replace('data_','search_',$cl->table_data);
    if($cl->pass($data)){
      $var = $cl->add($data);
      var_export($var);
      mlog($var);
    }else{
      mlog($do->errmsg);
    }
  }
  function parse_data($data){
    $keys = explode(',','style,brand,tag,keyword,pptword,thumb,thumb1,thumb2,email,msn,qq,skype,linkurl,filepath,notete');
    foreach($keys as $k){
      if(!array_key_exists($k,$data))
        $data[$k] = '';
    }
    $data['catid'] = 4;
    $data['status'] = 3;
    return $data; 
  }
}
