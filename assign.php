<?php
// $Header: /cvsroot/bitweaver/_bit_boards/assign.php,v 1.1 2006/07/12 17:00:32 hash9 Exp $
// Copyright (c) 2004 bitweaver BitBoard
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once( '../bit_setup_inc.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'bitboards' );

// Now check permissions to access this page
$gBitSystem->verifyPermission('p_bitboards_edit' );

require_once(BITBOARDS_PKG_PATH.'lookup_inc.php' );

if (!empty($_REQUEST['remove'])) {
	foreach ($_REQUEST['remove'] as $board_id => $content_ids) {
		$b = new BitBoard($board_id);
		$b->load();
		foreach ($content_ids as $content_id => $remove) {
			if ($remove) {
				$b->removeContent($content_id);
			}
		}
	}
}
if (!empty($_REQUEST['assign'])) {
	foreach ($_REQUEST['assign'] as $board_id => $content_ids) {
		$b = new BitBoard($board_id);
		$b->load();
		foreach ($content_ids as $content_id) {
			$b->addContent($content_id);
		}
	}
}
if (!empty($_REQUEST['integrity'])) {
	$board_id = $_REQUEST['integrity'];
	$b = new BitBoard($board_id);
	$b->load();
	$b->fixContentMap();
}

$data = BitBoard::getAllMap();
$gBitSmarty->assign_by_ref('data',$data);

// Display the template
$gBitSystem->display( 'bitpackage:bitboards/board_assign.tpl', tra('Assign content to Board') );
?>