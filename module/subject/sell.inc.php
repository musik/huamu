<?php 
defined('IN_DESTOON') or exit('Access Denied');
$TMOD = cache_read('module-'.$scat['moduleid'].'.php');
$SMOD = array_merge($SMOD,$TMOD);
$TYPE = explode('|', trim($SMOD['type']));
$seo_title = "供应信息_".$seo_title;
$maincat = get_maincat($scat['catid'], $scat['moduleid']);
$condition = 'status=3';
$condition .= ($scat['child']) ? " AND catid IN (".$scat['arrchildid'].")" : " AND catid=$scat[catid]";
$stable = get_table($scat['moduleid']);
if($cityid) {
	$areaid = $cityid;
	$ARE = $AREA[$cityid];
	$condition .= $ARE['child'] ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";
	$items = $db->count($stable, $condition, $CFG['db_expires']);
} else {
	if($page == 1) {
		$items = $db->count($stable, $condition, $CFG['db_expires']);
		if($items != $scat['item']) {
			$scat['item'] = $items;
			$db->query("UPDATE {$DT_PRE}category SET item=$items WHERE catid=$scat[catid]");
		}
	} else {
		$items = $scat['item'];
	}
}
$pagesize = $SMOD['pagesize'];
$offset = ($page-1)*$pagesize;
//MOD
$pages = subject_listpages($scat, $items, $page, $pagesize);
$tags = array();
if($items) {
	$result = $db->query("SELECT ".$SMOD['fields']." FROM {$stable} WHERE {$condition} ORDER BY ".$SMOD['order']." LIMIT {$offset},{$pagesize}", ($CFG['db_expires'] && $page == 1) ? 'CACHE' : '', $CFG['db_expires']);
	while($r = $db->fetch_array($result)) {
		$r['adddate'] = timetodate($r['addtime'], 5);
		$r['editdate'] = timetodate($r['edittime'], 5);
		if($lazy && isset($r['thumb']) && $r['thumb']) $r['thumb'] = DT_SKIN.'image/lazy.gif" original="'.$r['thumb'];
		$r['alt'] = $r['title'];
		$r['title'] = set_style($r['title'], $r['style']);
		$r['linkurl'] = $SMOD['linkurl'].$r['linkurl'];
		$tags[] = $r;
	}
	$db->free_result($result);
}
$showpage = 1;
$datetype = 5;
