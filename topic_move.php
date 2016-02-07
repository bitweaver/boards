<?php
/**
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

// Look up Topic (lookup_inc is universal, gContent == BitBoardTopic)
require_once( BOARDS_PKG_PATH.'lookup_inc.php' );

// Make sure topic exists since we only run through here for existing topics. New topics are created via comment system.
if( !$gContent->isValid() ){
	$gBitSystem->fatalError( 'No topic specified' );
}

// Load up the Topic's board - we'll respect its permissions
$board = new BitBoard( $gContent->mInfo['board_id'] );
$board->load();
$board->verifyAdminPermission();

if( isset( $_REQUEST["target"] ) ) {
	// Check the user's ticket
	$gBitUser->verifyTicket();

	$targetBoard = new BitBoard( null, $_REQUEST["target"] );
	$targetBoard->load();
	if( !$targetBoard->hasAdminPermission() ){
		$gBitSystem->fatalError( 'You do not have permission to move topics to the Board' . $targetBoard->mInfo['title'] );
	}

	if( $gContent->moveTo($_REQUEST["target"]) ) {
		bit_redirect( $gContent->getDisplayUrl() );
	} else {
		$gBitSystem->fatalError( "There was an error moving the topic: ".vc( $gContent->mErrors ));
	}
}

// get list of boards we can move the topic to
$boards = $board->getBoardSelectList();
$gBitSmarty->assignByRef('boards', $boards);

$gBitSmarty->assign('fromBoardId', $board->mContentId);

$gBitSystem->display( 'bitpackage:boards/topic_move.tpl', tra('Move Topic').':'.$gContent->getTitle(), array( 'display_mode' => 'display' ));
?>
