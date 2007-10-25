<?php
/**
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( BOARDS_PKG_PATH.'BitBoard.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_boards_edit' );


if( isset( $_REQUEST["confirm"] ) ) {
	require_once( BOARDS_PKG_PATH.'lookup_inc.php' );
	if( $gContent->moveTo($_REQUEST["target"]) ) {
		header ("location: ".$_REQUEST["ref"] );
		die;
	} else {
		$gBitSystem->fatalError( "There was an error moving the topic: ".vc( $gContent->mErrors ));
	}
}

if( isset( $_REQUEST["target"] ) ) {
	$_REQUEST["content_id"] = $_REQUEST["target"];
	require_once( BOARDS_PKG_PATH.'lookup_inc.php' );
	$bitThread = $gContent;
	unset($gContent);
	require_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
	$bitBoard = $gContent;

	$gBitSystem->setBrowserTitle( tra( 'Confirm moving' ).' "' .$bitThread->mInfo['title'] .'" '. tra("to Board"). ' "'.$bitBoard->mInfo['title'].'"');
	$formHash=array();
	if (empty($_REQUEST["ref"])) {
		$_REQUEST["ref"]=$_SERVER['HTTP_REFERER'];
	} elseif ($_REQUEST["ref"]=="-") {
		$_REQUEST["ref"]=$bitThread->getDisplayUrl();
	}
	$formHash["ref"]=$_REQUEST["ref"];
	$formHash["target"]=$_REQUEST["target"];
	$formHash["t"]=$_REQUEST["t"];
	$msgHash = array(
		'label' => tra( "Move Thread" ).": ".$bitThread->mInfo['title']  ,
		'confirm_item' => $bitThread->mInfo['title'] ,
		'warning' => tra( "Move ".' "' .$bitThread->mInfo['title'] .'" '. tra("to Board"). ' "'.$bitBoard->mInfo['title'].'"'."<br />This cannot be undone!" ),
	);
	$gBitSystem->confirmDialog( $formHash,$msgHash );
}

$board = new BitBoard();
$gBitSmarty->assign_by_ref('boards', $board->getBoardSelectList());
require_once( BOARDS_PKG_PATH .'lookup_inc.php' );

$gBitSystem->display( 'bitpackage:boards/topic_move.tpl', tra('Category') );
?>
