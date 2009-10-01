<?php
// $Header: /cvsroot/bitweaver/_bit_boards/admin/admin_boards_inc.php,v 1.10 2009/10/01 13:45:32 wjames5 Exp $
// Copyright (c) 2005 bitweaver BitBoards
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.

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

$formBitBoardsSync = array(
	'boards_sync_mail_server' => array(
		'label' => 'Email Server',
		'note' => 'Internet address of your mail server.',
	),
	'boards_sync_user' => array(
		'label' => 'Email Username',
		'note' => 'Username used to login to the board sync email account.',
	),
	'boards_sync_password' => array(
		'label' => 'Email Password',
		'note' => 'Password used to login to the board sync email account.',
	),
);
$gBitSmarty->assign( 'formBitBoardsSync',$formBitBoardsSync );
$processForm = set_tab();

$formBoardsEmailList = array(
	"boards_email_list" => array(
		'label' => 'Group Email List',
		'note' => 'Enable groups to have an associated email list',
	),
);
$gBitSmarty->assign( 'formBoardsEmailList',$formBoardsEmailList );
$formBoardsEmailText = array( 'boards_email_host', 'boards_email_admin', 'server_mailman_bin', 'server_mailman_cmd', 'server_newaliases_cmd', 'server_aliases_file' );

if( $processForm ) {
	$bitboardToggles = array_merge( $formBitBoardsLists,$formBoardsEmailList );
	foreach( $bitboardToggles as $item => $data ) {
		simple_set_toggle( $item, BOARDS_PKG_NAME );
	}
	foreach( $formBitBoardsSync as $key => $data ) {
		$gBitSystem->storeConfig( $key, (!empty( $_REQUEST[$key] ) ? $_REQUEST[$key] : NULL), BOARDS_PKG_NAME );
	}

	foreach( $formBoardsEmailText as $text ) {
		$gBitSystem->storeConfig( $text, ( !empty( $_REQUEST[$text] ) ? trim( $_REQUEST[$text] ) : NULL ), BOARDS_PKG_NAME );
	}
	
}

$board = new BitBoard();
$boards = $board->getBoardSelectList( $_REQUEST );
$gBitSmarty->assign_by_ref(BOARDS_PKG_NAME, $boards['data']);
?>
