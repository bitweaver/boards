<?php
// $Header: /cvsroot/bitweaver/_bit_boards/index.php,v 1.7 2007/03/31 15:54:13 squareing Exp $
// Copyright (c) 2004 bitweaver Messageboards
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../bit_setup_inc.php' );
require_once( BOARDS_PKG_PATH.'BitBoard.php' );
require_once( BOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BOARDS_PKG_PATH.'BitBoard.php' );

if( !empty( $_REQUEST['t'] ) || !empty( $_REQUEST['migrate_topic_id'] ) || !empty( $_REQUEST['migrate_post_id'] ) ) {
	require( BOARDS_PKG_PATH.'post.php' );
} elseif (!empty($_REQUEST['b']) || !empty( $_REQUEST['migrate_board_id'] ) ) {
	require( BOARDS_PKG_PATH.'topic.php' );
} else {
	require( BOARDS_PKG_PATH.'board.php' );
}

?>
