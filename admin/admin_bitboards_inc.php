<?php
// $Header: /cvsroot/bitweaver/_bit_boards/admin/Attic/admin_bitboards_inc.php,v 1.8 2006/08/31 13:36:30 squareing Exp $
// Copyright (c) 2005 bitweaver BitBoards
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// is this used?
//if (isset($_REQUEST["bitboardset"]) && isset($_REQUEST["homeBitBoards"])) {
//	$gBitSystem->storeConfig("home_bitboard", $_REQUEST["homeBitBoards"]);
//	$gBitSmarty->assign('home_bitboard', $_REQUEST["homeBitBoards"]);
//}

require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

$formBitBoardsLists = array(
	'bitboards_thread_track' => array(
		'label' => 'Enable Topic Status Tracking',
		'note' => 'Allow users to see what topic have been changed since they last logged on.',
	),
	'bitboards_thread_notification' => array(
		'label' => 'Enable Topic Reply Notification',
		'note' => 'Allow users to be sent emails when topics they are interested in recive replies.',
	),
	'bitboards_post_anon_moderation' => array(
		'label' => 'Enable Forced Anon Post Moderation',
		'note' => 'Require that ALL Anonymous posts must be validated before they are shown.',
	),
);
$gBitSmarty->assign( 'formBitBoardsLists',$formBitBoardsLists );

$processForm = set_tab();

if( $processForm ) {
	$bitboardToggles = array_merge( $formBitBoardsLists );
	foreach( $bitboardToggles as $item => $data ) {
		simple_set_toggle( $item, BITBOARDS_PKG_NAME );
	}

}

$board = new BitBoard();
$bitboards = $board->getBoardSelectList( $_REQUEST );
$gBitSmarty->assign_by_ref(BITBOARDS_PKG_NAME, $bitboards['data']);
?>
