<?php
// $Header: /cvsroot/bitweaver/_bit_boards/index.php,v 1.6 2007/03/07 21:40:29 spiderr Exp $
// Copyright (c) 2004 bitweaver Messageboards
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../bit_setup_inc.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

if( !empty( $_REQUEST['t'] ) || !empty( $_REQUEST['migrate_topic_id'] ) || !empty( $_REQUEST['migrate_post_id'] ) ) {
	require( BITBOARDS_PKG_PATH.'post.php' );
} elseif (!empty($_REQUEST['b']) || !empty( $_REQUEST['migrate_board_id'] ) ) {
	require( BITBOARDS_PKG_PATH.'topic.php' );
} else {
	require( BITBOARDS_PKG_PATH.'board.php' );
}

?>
