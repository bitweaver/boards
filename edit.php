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
require_once( '../kernel/setup_inc.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

if( isset( $_REQUEST['bitboard']['board_id'] ) ) {
	$_REQUEST['b'] = $_REQUEST['bitboard']['board_id'];
}

require_once(BOARDS_PKG_INCLUDE_PATH.'lookup_inc.php' );

//must be owner or admin to edit an existing board
if( $gContent->isValid() ) {
	$gContent->verifyUpdatePermission();
} else {
	$gContent->verifyCreatePermission();
}

// Handle delete request
if( isset( $_REQUEST['remove'] ) ) {
	// @TODO: Change to verifyExpungePermission when that exists in LibertyContent
	if ( $gContent->isValid() && $gContent->hasUserPermission( 'p_boards_remove', TRUE, TRUE ) ) {
		if( empty( $_REQUEST['confirm'] ) ) {
			$formHash['b'] = $_REQUEST['b'];
			$formHash['remove'] = TRUE;
			$gBitSystem->confirmDialog( $formHash, array( 'warning' => tra( 'Are you sure you want to remove the entire message board' ).' "'.$gContent->getTitle().'" ?', 'error' => 'This cannot be undone!' ) );
		} elseif( !$gContent->expunge() ) {
			$gBitSmarty->assignByRef( 'errors', $deleteComment->mErrors );
		} else {
			bit_redirect( BOARDS_PKG_URL.'index.php' );
		}
	} else {
		$gBitSystem->fatalPermission( 'p_boards_remove' );
	}
}

// If we are in preview mode then preview it!
if( isset( $_REQUEST["preview"] ) ) {
	$gBitSmarty->assign('preview', 'y');
	$previewHash = array_merge( $_REQUEST, $_REQUEST['bitboard'] );
	$gContent->preparePreview( $previewHash );
	$gContent->invokeServices( 'content_preview_function' );
} else {
	$gContent->invokeServices( 'content_edit_function' );
}

// Pro
// Check if the page has changed
if( !empty( $_REQUEST["save_bitboard"] ) ) {
	// merge our arrays so our storage hash works with LibertyContent storage of LibertyContent add ons.
	$storeHash = array_merge( $_REQUEST, $_REQUEST['bitboard'] );
	// Check if all Request values are delivered, and if not, set them
	// to avoid error messages. This can happen if some features are
	// disabled
	if( $gContent->store( $storeHash ) ) {
		$gContent->storePreference( 'board_sync_list_address', (!empty( $_REQUEST['bitboardconfig']['board_sync_list_address'] ) ?  $_REQUEST['bitboardconfig']['board_sync_list_address'] : NULL) );
		bit_redirect( $gContent->getDisplayUrl() );
	} else {
		$gBitSmarty->assignByRef( 'errors', $gContent->mErrors );
	}
}

// Display the template
$gBitSystem->display( 'bitpackage:boards/board_edit.tpl', tra('Board') , array( 'display_mode' => 'edit' ));
?>
