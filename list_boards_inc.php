<?php
/**
 * $Header$
 * Copyright (c) 2004 bitweaver Messageboards
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once("../kernel/setup_inc.php");
require_once( BOARDS_PKG_PATH.'BitBoardTopic.php' );
require_once( BOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BOARDS_PKG_PATH.'BitBoard.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_boards_read' );

// Get a list of boards
$ns = array();
$board_all_cids =array();

// @TODO move pigeonholes to its own file library or something
if($gBitSystem->isPackageActive('pigeonholes')) {
	require_once(PIGEONHOLES_PKG_PATH.'Pigeonholes.php');

	$p = new Pigeonholes();
	$s = new LibertyStructure();

	$listHash = array('load_only_root'=> TRUE);
	$l = $p->getList($listHash);
	foreach ($l as $e) {
		$d = $s->getSubTree( $e['structure_id'] );
		$d_o = array();
		foreach ($d as $c) {
			$pos_var = &$d_o;
			if($c['level']!=0) {
				$pos = explode(".",$c['pos']);
				$pos_var = &$d_o;
				foreach ($pos as $pos_v) {
					if (!isset($pos_var['children'])) {
						$pos_var['children']=array();
					}
					if (!isset($pos_var['children'][$pos_v-1])) {
						$pos_var['children'][$pos_v-1]=array();
					}
					$pos_var = &$pos_var['children'][$pos_v-1];
				}
			}
			if (empty($pos_var['data'])) {
				$pos_var['children']=array();
				$pos_var['data']=$c;
				$mlHash=array();
				$mlHash['content_id']=$c['content_id'];
				$mlHash['content_type_guid']=BITBOARD_CONTENT_TYPE_GUID;
				$pos_var['members']=$p->getMemberList($mlHash);
				$board_cids =array();
				foreach ($pos_var['members'] as $boardKey) {
					$board_cids[] = $boardKey['content_id'];
				}
				if (count($board_cids)>0) {
					$listHash = array('boards'=>$board_cids,'paginationOff'=>'y');
					$board = new BitBoard();
					$pos_var['members'] = $board->getList($listHash);
					$pos_var['pagination']=$listHash['listInfo'];
					$board_all_cids = array_merge($board_all_cids,$board_cids);
				}
			}
		}
		$ns[]=$d_o;
	}

}

// get our boards list
$ret =array();
if($gBitSystem->isPackageActive('pigeonholes')) {
//	$ret['data']['title']="Uncategorised Boards";
} else {
//	$ret['data']['title']="Board List";
}
$ret['children']=array();
$listHash = array('nboards'=>$board_all_cids,'paginationOff'=>'y');
$board = new BitBoard();
$ret['members'] = $board->getList($listHash);
if (count($ret['members'])>0) {
	$ns[] = $ret;
}

$gBitSmarty->assign_by_ref('ns',$ns);

// this might be for getting a count of nested boards - not entirely sure, if you figure it out please clarify this comment.
function countBoards(&$a) {
	$s = count($a['members']);
	foreach ($a['children'] as $k=>$c) {
		$n = countBoards($a['children'][$k]);
		if ($n == 0) {
			unset($a['children'][$k]);
		}
		else {
			$a['children'][$k]['sub_count'] = $n;
			$s += $n;
		}
	}
	return $s;
}

foreach ($ns as $k=> $a) {
	$n = countBoards($ns[$k]);
	if ($n == 0) {
		unset($ns[$k]);
	}
	else {
		$ns[$k]['sub_count'] = $n;
	}
}

//$gBitSmarty->display( 'bitpackage:boards/cat_display.tpl');
$gBitSystem->display( 'bitpackage:boards/list_boards.tpl', tra( 'Boards' ) , array( 'display_mode' => 'display' ));

?>
