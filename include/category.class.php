<?php
class category {
	var $moduleid;
	var $catid;
	var $category = array();
	var $db;
	var $table;

	function category($moduleid = 1, $catid = 0) {
		global $db, $DT_PRE, $CATEGORY;
		$this->moduleid = $moduleid;
		$this->catid = $catid;
		if(!isset($CATEGORY)) $CATEGORY = cache_read('category-'.$this->moduleid.'.php');
		$this->category = $CATEGORY;
		$this->table = $DT_PRE.'category';
		$this->db = &$db;
	}
  function exists($condition = "1"){
    return $this->db->get_one("select * from $this->table where $condition");
  }

	function add($category)	{
		$category['moduleid'] = $this->moduleid;
		$category['letter'] = preg_match("/^[a-z]{1}+$/i", $category['letter']) ? strtolower($category['letter']) : '';
		foreach(array('group_list',  'group_show',  'group_add') as $v) {
			$category[$v] = isset($category[$v]) ? implode(',', $category[$v]) : '';
		}
		$sqlk = $sqlv = '';
		foreach($category as $k=>$v) {
			$sqlk .= ','.$k; $sqlv .= ",'$v'"; 
		}
        $sqlk = substr($sqlk, 1);
        $sqlv = substr($sqlv, 1);
		$this->db->query("INSERT INTO {$this->table} ($sqlk) VALUES ($sqlv)");		
		$this->catid = $this->db->insert_id();
		if($category['parentid']) {
			$category['catid'] = $this->catid;
			$this->category[$this->catid] = $category;
			$arrparentid = $this->get_arrparentid($this->catid, $this->category);
		} else {
			$arrparentid = 0;
		}
    //muzik hacked:批量添加分类时自动生成拼音slug和首字母
		$catdir = $category['catdir'] ? $category['catdir'] : $this->get_catdir($this->get_letter($category['catname'],false));
    $letter = substr($catdir,0,1);
		$this->db->query("UPDATE {$this->table} SET listorder=$this->catid,catdir='$catdir',letter='$letter',arrparentid='$arrparentid' WHERE catid=$this->catid");
		//$catdir = $category['catdir'] ? $category['catdir'] : $this->catid;
		//$this->db->query("UPDATE {$this->table} SET listorder=$this->catid,catdir='$catdir',arrparentid='$arrparentid' WHERE catid=$this->catid");
		return true;
	}

	function edit($category) {
		$category['letter'] = preg_match("/^[a-z]{1}+$/i", $category['letter']) ? strtolower($category['letter']) : '';
		if($category['parentid']) {
			$category['catid'] = $this->catid;
			$this->category[$this->catid] = $category;
			$category['arrparentid'] = $this->get_arrparentid($this->catid, $this->category);
		} else {
			$category['arrparentid'] = 0;
		}
		foreach(array('group_list',  'group_show',  'group_add') as $v) {
			$category[$v] = isset($category[$v]) ? implode(',', $category[$v]) : '';
		}
		$category['linkurl'] = '';
		$sql = '';
		foreach($category as $k=>$v) {
			$sql .= ",$k='$v'";
		}
		$sql = substr($sql, 1);
		$this->db->query("UPDATE {$this->table} SET $sql WHERE catid=$this->catid");
		return true;
	}

	function delete($catids) {
		if(is_array($catids)) {
			foreach($catids as $catid) {
				if(isset($this->category[$catid])) $this->delete($catid);
			}
		} else {
			$catid = $catids;
			if(isset($this->category[$catid])) {
				$this->db->query("DELETE FROM {$this->table} WHERE catid=$catid");
				$arrchildid = $this->category[$catid]['arrchildid'] ? $this->category[$catid]['arrchildid'] : $catid;
				$this->db->query("DELETE FROM {$this->table} WHERE catid IN ($arrchildid)");			
				if($this->moduleid > 4) $this->db->query("UPDATE ".get_table($this->moduleid)." SET status=0 WHERE catid IN (".$arrchildid.")");
			}
		}
		return true;
	}

	function update($category) {
	    if(!is_array($category)) return false;
		foreach($category as $k=>$v) {
			if(!$v['catname']) continue;
			$v['parentid'] = intval($v['parentid']);
			if($k == $v['parentid']) continue;
			if($v['parentid'] > 0 && !isset($this->category[$v['parentid']])) continue;
			$v['listorder'] = intval($v['listorder']);
			$v['level'] = intval($v['level']);
			$v['letter'] = preg_match("/^[a-z0-9]{1}+$/i", $v['letter']) ? strtolower($v['letter']) : '';
			$v['catdir'] = $this->get_catdir($v['catdir'], $k);
			if(!$v['catdir']) $v['catdir'] = $k;
			$this->db->query("UPDATE {$this->table} SET catname='$v[catname]',parentid='$v[parentid]',listorder='$v[listorder]',style='$v[style]',level='$v[level]',letter='$v[letter]',catdir='$v[catdir]' WHERE catid=$k ");
		}
		return true;
	}

	function repair() {
		$query = $this->db->query("SELECT * FROM {$this->table} WHERE moduleid='$this->moduleid' ORDER BY listorder,catid");
		$CATEGORY = array();
		while($r = $this->db->fetch_array($query)) {
			$CATEGORY[$r['catid']] = $r;
		}
		$childs = array();
		foreach($CATEGORY as $catid => $category) {
			$CATEGORY[$catid]['arrparentid'] = $arrparentid = $this->get_arrparentid($catid, $CATEGORY);
			$CATEGORY[$catid]['catdir'] = $catdir = preg_match("/^[0-9a-z_\-\/]+$/i", $category['catdir']) ? $category['catdir'] : $catid;
			$sql = "catdir='$catdir',arrparentid='$arrparentid'";
			if(!$category['linkurl']) {
				$CATEGORY[$catid]['linkurl'] = listurl($category);
				$sql .= ",linkurl='$category[linkurl]'";
			}
			$this->db->query("UPDATE {$this->table} SET $sql WHERE catid=$catid");
			if($arrparentid) {
				$arr = explode(',', $arrparentid);
				foreach($arr as $a) {
					if($a == 0) continue;
					isset($childs[$a]) or $childs[$a] = '';
					$childs[$a] .= ','.$catid;
				}
			}
		}
		foreach($CATEGORY as $catid => $category) {
			if(isset($childs[$catid])) {
				$CATEGORY[$catid]['arrchildid'] = $arrchildid = $catid.$childs[$catid];
				$CATEGORY[$catid]['child'] = 1;
				$this->db->query("UPDATE {$this->table} SET arrchildid='$arrchildid',child=1 WHERE catid='$catid'");
			} else {
				$CATEGORY[$catid]['arrchildid'] = $catid;
				$CATEGORY[$catid]['child'] = 0;
				$this->db->query("UPDATE {$this->table} SET arrchildid='$catid',child=0 WHERE catid='$catid'");
			}
		}
		$this->cache($CATEGORY);
        return true;
	}

	function get_arrparentid($catid, $CATEGORY) {
		if($CATEGORY[$catid]['parentid'] && $CATEGORY[$catid]['parentid'] != $catid) {
			$parents = array();
			$cid = $catid;
			while($catid) {
				if($CATEGORY[$cid]['parentid']) {
					$parents[] = $cid = $CATEGORY[$cid]['parentid'];
				} else {
					break;
				}
			}
			$parents[] = 0;
			return implode(',', array_reverse($parents));
		} else {
			return '0';
		}
	}

	function get_arrchildid($catid, $CATEGORY) {
		$arrchildid = '';
		foreach($CATEGORY as $category) {
			if(strpos(','.$category['arrparentid'].',', ','.$catid.',') !== false) $arrchildid .= ','.$category['catid'];
		}
		return $arrchildid ? $catid.$arrchildid : $catid;
	}

	function get_catdir($catdir, $catid = 0) {
		if(preg_match("/^[0-9a-z_\-\/]+$/i", $catdir)) {
			$condition = "catdir='$catdir' AND moduleid='$this->moduleid'";
			if($catid) $condition .= " AND catid!=$catid";
			$r = $this->db->get_one("SELECT catid FROM {$this->table} WHERE $condition");
			if($r) {
				return '';
			} else {
				return $catdir;
			}
		} else {
			return '';
		}
	}

	function get_letter($catname, $letter = true) {
		return $letter ? strtolower(substr(gb2py($catname), 0, 1)) : str_replace(' ', '', gb2py($catname));
	}

	function cache($data = array()) {
		cache_category($this->moduleid, $data);
	}
}
?>
