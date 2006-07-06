<?php
// $Header: /cvsroot/bitweaver/_bit_boards/admin/Attic/admin_bitboards_inc.php,v 1.2 2006/07/06 14:31:22 hash9 Exp $
// Copyright (c) 2005 bitweaver BitForum
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// is this used?
//if (isset($_REQUEST["bitboardset"]) && isset($_REQUEST["homeBitForum"])) {
//	$gBitSystem->storeConfig("home_bitboard", $_REQUEST["homeBitForum"]);
//	$gBitSmarty->assign('home_bitboard', $_REQUEST["homeBitForum"]);
//}

require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

$formBitForumLists = array(
	"bitboard_list_bitforum_id" => array(
		'label' => 'Id',
		'note' => 'Display the bitboard id.',
	),
	"bitboard_list_title" => array(
		'label' => 'Title',
		'note' => 'Display the title.',
	),
	"bitboard_list_description" => array(
		'label' => 'Description',
		'note' => 'Display the description.',
	),
	"bitboard_list_data" => array(
		'label' => 'Text',
		'note' => 'Display the text.',
	),
);
$gBitSmarty->assign( 'formBitForumLists',$formBitForumLists );

$processForm = set_tab();

if( $processForm ) {
	$bitboardToggles = array_merge( $formBitForumLists );
	foreach( $bitboardToggles as $item => $data ) {
		simple_set_toggle( $item, 'bitboards' );
	}

}

$bitboard = new BitBoard();
$bitboards = $bitboard->getForumBoardSelectList( $_REQUEST );
$gBitSmarty->assign_by_ref('bitboards', $bitforums['data']);
?>
