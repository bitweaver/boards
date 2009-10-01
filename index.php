<?php
// $Header: /cvsroot/bitweaver/_bit_boards/index.php,v 1.10 2009/10/01 13:45:32 wjames5 Exp $
// Copyright (c) 2004 bitweaver Messageboards
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// Initialization
require_once( '../bit_setup_inc.php' );

if( !empty( $_REQUEST['t'] ) || !empty( $_REQUEST['migrate_topic_id'] ) || !empty( $_REQUEST['migrate_post_id'] ) ) {
	require( BOARDS_PKG_PATH.'view_topic_inc.php' );
} elseif (!empty($_REQUEST['b']) || !empty( $_REQUEST['migrate_board_id'] ) ) {
	require( BOARDS_PKG_PATH.'view_board_inc.php' );
} else {
	require( BOARDS_PKG_PATH.'list_boards_inc.php' );
}

?>
