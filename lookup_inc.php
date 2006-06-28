<?php

require_once( BITBOARDS_PKG_PATH.'BitBoardTopic.php');
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );

// if p supplied, use that
if( @BitBase::verifyId( $_REQUEST['t'] ) ) {
	$gContent = new BitBoardTopic( $_REQUEST['t'] );

	// if t supplied, use that
} elseif( @BitBase::verifyId( $_REQUEST['p'] ) ) {
	$gContent = new BitBoardPost( $_REQUEST['p'] );
} elseif (isset($_REQUEST['p'])) {
	$gContent = new BitBoardPost();
	// otherwise create new object
} else {
	$gContent = new BitBoardTopic();
}

$gContent->load();
$gBitSmarty->assign_by_ref( "gContent", $gContent );

?>