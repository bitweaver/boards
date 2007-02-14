<?php
// $Header: /cvsroot/bitweaver/_bit_boards/index.php,v 1.3 2007/02/14 08:19:27 bitweaver Exp $
// Copyright (c) 2004 bitweaver Messageboards
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../bit_setup_inc.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

if (!empty($_REQUEST['t'])) {
	require( BITBOARDS_PKG_PATH.'post.php' );
} elseif (!empty($_REQUEST['b'])) {
	require( BITBOARDS_PKG_PATH.'topic.php' );
} else {
	require( BITBOARDS_PKG_PATH.'board.php' );
}

?>
