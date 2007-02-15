<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_boards/edit.php,v 1.3 2007/02/15 19:36:12 lsces Exp $
 * Copyright (c) 2004 bitweaver Messageboards
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'bitboards' );

// Now check permissions to access this page
$gBitSystem->verifyPermission('p_bitboards_edit' );

if( isset( $_REQUEST['bitboard']['board_id'] ) ) {
	$_REQUEST['b'] = $_REQUEST['bitboard']['board_id'];
}

require_once(BITBOARDS_PKG_PATH.'lookup_inc.php' );

if( isset( $_REQUEST['bitboard']["title"] ) ) {
	$gContent->mInfo["title"] = $_REQUEST['bitboard']["title"];
}

if( isset( $_REQUEST['bitboard']["description"] ) ) {
	$gContent->mInfo["description"] = $_REQUEST['bitboard']["description"];
}

if( isset( $_REQUEST["format_guid"] ) ) {
	$gContent->mInfo['format_guid'] = $_REQUEST["format_guid"];
}

if( isset( $_REQUEST['bitboard']["edit"] ) ) {
	$gContent->mInfo["data"] = $_REQUEST['bitboard']["edit"];
	$gContent->mInfo['parsed_data'] = $gContent->parseData();
}

// If we are in preview mode then preview it!
if( isset( $_REQUEST["preview"] ) ) {
	$gBitSmarty->assign('preview', 'y');
	$gContent->invokeServices( 'content_preview_function' );
} else {
	$gContent->invokeServices( 'content_edit_function' );
}

// Pro
// Check if the page has changed
if( !empty( $_REQUEST["save_bitboard"] ) ) {
	// Check if all Request values are delivered, and if not, set them
	// to avoid error messages. This can happen if some features are
	// disabled
	if( $gContent->store( $_REQUEST['bitboard'] ) ) {
		header( "Location: ".$gContent->getDisplayUrl() );
		die;
	} else {
		$gBitSmarty->assign_by_ref( 'errors', $gContent->mErrors );
	}
}

// Configure quicktags list
if( $gBitSystem->isPackageActive( 'quicktags' ) ) {
	include_once( QUICKTAGS_PKG_PATH.'quicktags_inc.php' );
}

// WYSIWYG and Quicktag variable
$gBitSmarty->assign( 'textarea_id', 'editbitboard' );

// Display the template
$gBitSystem->display( 'bitpackage:bitboards/board_edit.tpl', tra('Board') );
?>
