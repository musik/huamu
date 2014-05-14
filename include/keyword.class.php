<?php
class keyword {
	var $db;
	var $table;

	function keyword() {
		global $db, $DT_PRE;
		$this->table = $DT_PRE.'keyword';
		$this->db = &$db;
	}

	function get_list($condition, $order) {
		global $pages, $page, $pagesize, $offset, $pagesize;
		$pages = pages($this->db->count($this->table, $condition), $page, $pagesize);
		$lists = array();
		$result = $this->db->query("SELECT * FROM {$this->table} WHERE $condition ORDER BY $order LIMIT $offset,$pagesize");
		while($r = $this->db->fetch_array($result)) {
			$lists[] = $r;
		}
		return $lists;
	}
  function update_sells($name,$sell_id){
    $r = $this->db->get_one("select itemid,sellids from $this->table where word = '$name'");
    if($r){
      $sell_ids = $r["sellids"] ? explode(",",$r["sellids"]) : array();
      if(!in_array($sell_id,$sell_ids)){
        $sell_ids[] = $sell_id;
        $sell_ids = implode(',',$sell_ids);
        $this->db->query("update $this->table set sellids = '$sell_ids' where itemid = $r[itemid]");
      }
    }
  }

	function update($post) {
		$this->add($post[0]);
		unset($post[0]);
		foreach($post as $k=>$v) {
			if(isset($v['delete'])) {
				$this->delete($k);
				unset($post[$k]);
			}
		}
		$this->edit($post);
	}

	function add($post) {
		global $DT_TIME;
		if(!$post['word']) return false;
		$post['status'] = $post['status'] == 3 ? 3 : 2;
		$this->db->query("INSERT INTO {$this->table} (moduleid,word,keyword,letter,items,total_search,month_search,week_search,today_search,updatetime,status) VALUES('$post[moduleid]','$post[word]','$post[keyword]','$post[letter]','$post[items]','$post[total_search]','$post[month_search]','$post[week_search]','$post[today_search]','$DT_TIME', '$post[status]')");
	}

	function edit($post) {
		foreach($post as $k=>$v) {
			if(!$v['word']) continue;
			$v['status'] = $v['status'] == 3 ? 3 : 2;
			$this->db->query("UPDATE {$this->table} SET word='$v[word]',keyword='$v[keyword]',letter='$v[letter]',total_search='$v[total_search]',month_search='$v[month_search]',week_search='$v[week_search]',today_search='$v[today_search]',status='$v[status]' WHERE itemid='$k'");
		}
	}

	function delete($itemid) {
		$this->db->query("DELETE FROM {$this->table} WHERE itemid=$itemid");
	}
  function check_install(){
    $r = $this->db->get_one("show columns from $this->table like 'ali_cat'");
    if($r) return false;
    $this->db->query("alter table $this->table add column ali_cat integer(11)");
    return true;
  }
}
?>
