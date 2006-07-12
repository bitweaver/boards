<?php
// $Header: /cvsroot/bitweaver/_bit_boards/edit.php,v 1.1 2006/07/12 17:00:32 hash9 Exp $
// Copyright (c) 2004 bitweaver BitBoard
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once( '../bit_setup_inc.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'bitboards' );

// Now check permissions to access this page
$gBitSystem->verifyPermission('p_bitboards_edit' );

if( isset( $_REQUEST['bitforum']['board_id'] ) ) {
	$_REQUEST['b'] = $_REQUEST['bitforum']['board_id'];
}

require_once(BITBOARDS_PKG_PATH.'lookup_inc.php' );

if( isset( $_REQUEST['bitforum']["title"] ) ) {
	$gContent->mInfo["title"] = $_REQUEST['bitforum']["title"];
}

if( isset( $_REQUEST['bitforum']["description"] ) ) {
	$gContent->mInfo["description"] = $_REQUEST['bitforum']["description"];
}

if( isset( $_REQUEST["format_guid"] ) ) {
	$gContent->mInfo['format_guid'] = $_REQUEST["format_guid"];
}

if( isset( $_REQUEST['bitforum']["edit"] ) ) {
	$gContent->mInfo["data"] = $_REQUEST['bitforum']["edit"];
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
if( !empty( $_REQUEST["save_bitforum"] ) ) {
	// Check if all Request values are delivered, and if not, set them
	// to avoid error messages. This can happen if some features are
	// disabled
	if( $gContent->store( $_REQUEST['bitforum'] ) ) {
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
$gBitSmarty->assign( 'textarea_id', 'editbitforum' );

// Display the template
$gBitSystem->display( 'bitpackage:bitboards/board_edit.tpl', tra('Board') );
?>
