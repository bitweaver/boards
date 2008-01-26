<?php
// $Header: /cvsroot/bitweaver/_bit_boards/admin/admin_boards_inc.php,v 1.5 2008/01/26 21:36:20 nickpalmer Exp $
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
	'boards_hide_edit_tpl' => array(
		'label' => 'Hide Linked Boards Option',
		'note' => 'Hide the <em>Linked Boards</em> option on edit pages to link any given content to a forum thread. If you hide this, you will have to manually assign content to a forum thread if you want to make full use of the boards.',
	),
	'boards_link_by_pigeonholes' => array(
		'label' => 'Link by Pigeonholes',
		'note' => 'Link content to boards based on pigeonholes. This is useful when you have only one board in a given pigeonhole and want all content in the same pigeonhole to be shown in the board. Note that you MUST run the following SQL on your database to allow content in more than one board: "alter table boards_map drop constraint boards_map_pkey;" This is therfore a very advanced option.'
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
