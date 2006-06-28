<?php
// $Header: /cvsroot/bitweaver/_bit_boards/Attic/board.php,v 1.1 2006/06/28 15:45:26 spiderr Exp $
// Copyright (c) 2004 bitweaver Messageboards
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'bitboard' );

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_bitboard_read' );

/* mass-remove:
the checkboxes are sent as the array $_REQUEST["checked[]"], values are the wiki-PageNames,
e.g. $_REQUEST["checked"][3]="HomePage"
$_REQUEST["submit_mult"] holds the value of the "with selected do..."-option list
we look if any page's checkbox is on and if remove_bitboards is selected.
then we check permission to delete bitboards.
if so, we call histlib's method remove_all_versions for all the checked bitboards.
*/

if( isset( $_REQUEST["submit_mult"] ) && isset( $_REQUEST["checked"] ) && $_REQUEST["submit_mult"] == "remove_bitboards" ) {

	// Now check permissions to remove the selected bitboard
	$gBitSystem->verifyPermission( 'p_bitboard_remove' );

	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['delete'] = TRUE;
		$formHash['submit_mult'] = 'remove_bitboards';
		foreach( $_REQUEST["checked"] as $del ) {
			$formHash['input'][] = '<input type="hidden" name="checked[]" value="'.$del.'"/>';
		}
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete '.count( $_REQUEST["checked"] ).' Boards?', 'error' => 'This cannot be undone!' ) );
	} else {
		foreach( $_REQUEST["checked"] as $deleteId ) {
			$tmpPage = new BitMBBoard( $deleteId );
			if( !$tmpPage->load() || !$tmpPage->expunge() ) {
				array_merge( $errors, array_values( $tmpPage->mErrors ) );
			}
		}
		if( !empty( $errors ) ) {
			$gBitSmarty->assign_by_ref( 'errors', $errors );
		}
	}
}

// create new bitboard object
$boards = new BitBoard();
$boardsList = $boards->getFullList( $_REQUEST );
$gBitSmarty->assign_by_ref( 'boardsList', $boardsList );
if (!empty($_REQUEST['content_type_guid'])) {
	$gBitSmarty->assign_by_ref( 'cType', $gLibertySystem->mContentTypes[$_REQUEST['content_type_guid']] );
}
// getList() has now placed all the pagination information in $_REQUEST['listInfo']
$gBitSmarty->assign_by_ref( 'listInfo', $_REQUEST['listInfo'] );

// Display the template
$gBitSystem->display( 'bitpackage:bitboard/board.tpl', tra( 'Forums' ) );

?>
