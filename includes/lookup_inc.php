<?php
/**
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( BOARDS_PKG_CLASS_PATH.'BitBoardTopic.php');
require_once( BOARDS_PKG_CLASS_PATH.'BitBoardPost.php' );
require_once( BOARDS_PKG_CLASS_PATH.'BitBoard.php' );
// if t supplied, use that
if( @BitBase::verifyId( $_REQUEST['t'] ) ) {
	$gContent = new BitBoardTopic( $_REQUEST['t'] );
// if p supplied, use that
} elseif( @BitBase::verifyId( $_REQUEST['p'] ) ) {
	$gContent = new BitBoardPost( $_REQUEST['p'] );
} elseif( @BitBase::verifyId( $_REQUEST['b'] ) ) {
	$gContent = new BitBoard( $_REQUEST['b'] );
} elseif (isset($_REQUEST['p'])) {
	$gContent = new BitBoardPost();
	// otherwise create new object
} elseif( isset( $_REQUEST['t'] ) || isset( $_REQUEST['migrate_topic_id'] ) || isset( $_REQUEST['migrate_post_id'] ) ) {
	$gContent = new BitBoardTopic();
} else {
	$gContent = new BitBoard();
}

$gContent->load();
$gBitSmarty->assignByRef( "gContent", $gContent );

?>
