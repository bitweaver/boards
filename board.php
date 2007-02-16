<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_boards/Attic/board.php,v 1.10 2007/02/16 18:08:29 nickpalmer Exp $
 * Copyright (c) 2004 bitweaver Messageboards
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once("../bit_setup_inc.php");
require_once( BITBOARDS_PKG_PATH.'BitBoardTopic.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'bitboards' );

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_bitboards_read' );

$ns = array();
$board_all_cids =array();

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
					$blHash = array('boards'=>$board_cids,'paginationOff'=>'y');
					$b = new BitBoard();
					$pos_var['members'] = $b->getList($blHash);
					$pos_var['pagination']=$blHash['listInfo'];
					$board_all_cids = array_merge($board_all_cids,$board_cids);
				}
			}
		}
		$ns[]=$d_o;
	}

}

$rest =array();
if($gBitSystem->isPackageActive('pigeonholes')) {
//	$rest['data']['title']="Uncategoried Boards";
} else {
//	$rest['data']['title']="Board List";
}
$rest['children']=array();
$blHash = array('nboards'=>$board_all_cids,'paginationOff'=>'y');
$b = new BitBoard();
$rest['members'] = $b->getList($blHash);
if (count($rest['members'])>0) {
	$ns[] = $rest;
}

$gBitSmarty->assign_by_ref('ns',$ns);

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

//$gBitSmarty->display( 'bitpackage:bitboards/cat_display.tpl');
$gBitSystem->display( 'bitpackage:bitboards/list_boards.tpl', tra( 'Boards' ) );

?>
