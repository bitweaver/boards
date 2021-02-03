<?php
// $Header$
// Copyright (c) 2004 bitweaver Messageboards
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// Initialization
require_once( '../kernel/includes/setup_inc.php' );
$gBitSystem->verifyPackage( 'boards' );

if( !empty( $_REQUEST['t'] ) || !empty( $_REQUEST['migrate_topic_id'] ) || !empty( $_REQUEST['migrate_post_id'] ) ) {
	require( BOARDS_PKG_INCLUDE_PATH.'view_topic_inc.php' );
} elseif (!empty($_REQUEST['b']) || !empty( $_REQUEST['migrate_board_id'] ) ) {
	require( BOARDS_PKG_INCLUDE_PATH.'view_board_inc.php' );
} else {
	require( BOARDS_PKG_INCLUDE_PATH.'list_boards_inc.php' );
}
