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

	if( isset( $_REQUEST["confirm"] ) ) {
		if( $gContent->moveTo($_REQUEST["target"]) ) {
			header ("location: ".$gContent-getDisplayUrl() );
			die;
		} else {
			$gBitSystem->fatalError( "There was an error moving the topic: ".vc( $gContent->mErrors ));
		}
	}else{
		$gBitSystem->setBrowserTitle( tra( 'Confirm moving' ).' "' .$gContent->mInfo['title'] .'" '. tra("to Board"). ' "'.$targetBoard->mInfo['title'].'"');
		$formHash=array();
		if (empty($_REQUEST["ref"])) {
			$_REQUEST["ref"]=$_SERVER['HTTP_REFERER'];
		} elseif ($_REQUEST["ref"]=="-") {
			$_REQUEST["ref"]=$gContent->getDisplayUrl();
		}
		$formHash["ref"]=$_REQUEST["ref"];
		$formHash["target"]=$_REQUEST["target"];
		$formHash["t"]=$_REQUEST["t"];
		$msgHash = array(
			'label' => tra( "Move Thread" ).": ".$gContent->mInfo['title']  ,
// redundant to title in tpl
//			'confirm_item' => $gContent->mInfo['title'] ,
			'warning' => tra( "Move ".' "' .$gContent->mInfo['title'] .'" '. tra("to Board"). ' "'.$targetBoard->mInfo['title'].'"'."<br />This cannot be undone!" ),
		);
		$gBitSystem->confirmDialog( $formHash,$msgHash );
	}
}

// get list of boards we can move the topic to
$boards = $board->getBoardSelectList();
$gBitSmarty->assign_by_ref('boards', $boards);

$gBitSmarty->assign('fromBoardId', $board->mContentId);

$gBitSystem->display( 'bitpackage:boards/topic_move.tpl', tra('Category') , array( 'display_mode' => 'display' ));
?>
