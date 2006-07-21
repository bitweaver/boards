<?php
// $Header: /cvsroot/bitweaver/_bit_boards/admin/Attic/admin_bitboards_inc.php,v 1.4 2006/07/21 23:58:44 hash9 Exp $
// Copyright (c) 2005 bitweaver BitForum
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// is this used?
//if (isset($_REQUEST["bitboardset"]) && isset($_REQUEST["homeBitForum"])) {
//	$gBitSystem->storeConfig("home_bitboard", $_REQUEST["homeBitForum"]);
//	$gBitSmarty->assign('home_bitboard', $_REQUEST["homeBitForum"]);
//}

require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardForum.php' );

$formBitForumLists = array(
	"bitboards_list_bitforum_id" => array(
		'label' => 'Id',
		'note' => 'Display the bitboard id.',
	),
	"bitboards_list_title" => array(
		'label' => 'Title',
		'note' => 'Display the title.',
	),
	"bitboards_list_description" => array(
		'label' => 'Description',
		'note' => 'Display the description.',
	),
	"bitboards_list_data" => array(
		'label' => 'Text',
		'note' => 'Display the text.',
	),
	'bitboards_thread_track' => array(
		'label' => 'Enable Thread Status Tracking',
		'note' => 'Allow users to see what threads have been changed since they last logged on',
	),
	'bitboards_thread_notification' => array(
		'label' => 'Enable Thread Reply Notification',
		'note' => 'Allow users to be sent emails when threads they are interested in recive replies',
	),
);
$gBitSmarty->assign( 'formBitForumLists',$formBitForumLists );

$processForm = set_tab();

if( $processForm ) {
	$bitboardToggles = array_merge( $formBitForumLists );
	foreach( $bitboardToggles as $item => $data ) {
		simple_set_toggle( $item, BITBOARDS_PKG_NAME );
	}

}

$bitboardforum = new BitBoardForum();
$bitboards = $bitboardforum->getForumBoardSelectList( $_REQUEST );
$gBitSmarty->assign_by_ref(BITBOARDS_PKG_NAME, $bitforums['data']);
?>
