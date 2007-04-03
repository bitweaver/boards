<?php
// $Header: /cvsroot/bitweaver/_bit_boards/admin/admin_boards_inc.php,v 1.1 2007/03/31 15:54:13 squareing Exp $
// Copyright (c) 2005 bitweaver BitBoards
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// is this used?
//if (isset($_REQUEST["boardset"]) && isset($_REQUEST["homeBitBoards"])) {
//	$gBitSystem->storeConfig("home_bitboard", $_REQUEST["homeBitBoards"]);
//	$gBitSmarty->assign('home_bitboard', $_REQUEST["homeBitBoards"]);
//}

require_once( BOARDS_PKG_PATH.'BitBoard.php' );

$formBitBoardsLists = array(
	'boards_thread_track' => array(
		'label' => 'Enable Topic Status Tracking',
		'note' => 'Allow users to see what topic have been changed since they last logged on.',
	),
	'boards_thread_notification' => array(
		'label' => 'Enable Topic Reply Notification',
		'note' => 'Allow users to be sent emails when topics they are interested in receive replies.',
	),
	'boards_posts_anon_moderation' => array(
		'label' => 'Enable Forced Anon Post Moderation',
		'note' => 'Require that ALL Anonymous posts must be validated before they are shown.',
	),
);
$gBitSmarty->assign( 'formBitBoardsLists',$formBitBoardsLists );

$processForm = set_tab();

if( $processForm ) {
	$bitboardToggles = array_merge( $formBitBoardsLists );
	foreach( $bitboardToggles as $item => $data ) {
		simple_set_toggle( $item, BOARDS_PKG_NAME );
	}

}

$board = new BitBoard();
$boards = $board->getBoardSelectList( $_REQUEST );
$gBitSmarty->assign_by_ref(BOARDS_PKG_NAME, $boards['data']);
?>