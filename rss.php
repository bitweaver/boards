<?php
/**
 * $Header$
 *
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

$gBitSystem->verifyPackage( 'boards' );
$gBitSystem->verifyPackage( 'rss' );

$feedFormat = array(
	0 => "RSS 0.91",
	1 => "RSS 1.0",
	2 => "RSS 2.0",
	3 => "PIE 0.1",
	4 => "MBOX",
	5 => "ATOM",
	6 => "ATOM 0.3",
	7 => "OPML",
	8 => "HTML",
	9 => "JS",
);
$gBitSmarty->assign( "feedFormat", $feedFormat );

// Load up the board or topic
require_once( BOARDS_PKG_PATH.'lookup_inc.php' );

if( !empty( $_REQUEST['get_feed'] ) ) {
	$feedlink['url'] = BOARDS_PKG_URL.'boards_rss.php?';
	if( $gContent->isValid() ){
		if( !empty( $_REQUEST['t'] ) ){
			$feedlink['url'] .= 't='.$_REQUEST['t']."&";
		}elseif( !empty($_REQUEST['b'] ) ){
			$feedlink['url'] .= 'b='.$_REQUEST['b']."&";
		} 
	}
	$feedlink['url'] .= 'version='.$_REQUEST['format'].( $gBitSystem->getConfig( 'rssfeed_httpauth' ) && $gBitUser->isRegistered()?'&httpauth=y':'');
	$feedlink['title'] = ( $gContent->getField('title') != NULL ?$gContent->getField('title'):tra('Boards')).' - '.$feedFormat[$_REQUEST['format']];
	$feedlink['format'] = $_REQUEST['format'];
} else {
	$feedlink['format'] = $gBitSystem->getConfig( 'rssfeed_default_version' );
}

$gBitSmarty->assign( 'feedlink', $feedlink );

$gBitSystem->display( 'bitpackage:boards/boards_rss_form.tpl', tra( 'Select Feed' ) , array( 'display_mode' => 'display' ));
?>
