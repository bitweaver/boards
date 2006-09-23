<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_bitboards/bitboards_rss.php,v 1.3 2006/05/04 18:43:22 squareing Exp $
 * @package wiki
 * @subpackage functions
 */

/**
 * Initialization
 */
require_once( "../bit_setup_inc.php" );
require_once( RSS_PKG_PATH."rss_inc.php" );
require_once( BITBOARDS_PKG_PATH."BitBoard.php" );
require_once( BITBOARDS_PKG_PATH."BitBoardTopic.php" );

$gBitSystem->verifyPackage( 'bitboards' );
$gBitSystem->verifyPackage( 'rss' );

if(!empty($_REQUEST['u'])) {
	$gBitUser->login($_REQUEST['u'],$_REQUEST['p']);
}

$board = new BitBoard($_REQUEST['b']);
$board->load();
$board->parseData();

$rss->title = $board->getField( 'title' )." Feed";
if ($gBitUser->isRegistered()) {
	$rss->title = $rss->title. " (".$gBitUser->getDisplayName().")";
}

$rss->description = $board->getField( 'parsed_data' );
$rss->link =  'http://'.$_SERVER['HTTP_HOST'].$board->getDisplayUrl();

// check if we want to use the cache file
$cacheFile = TEMP_PKG_PATH.RSS_PKG_NAME.'/'.BITBOARDS_PKG_NAME.'_'.$version.'.xml';
$rss->useCached( $cacheFile ); // use cached version if age < 1 hour

$topic = new BitBoardTopic();
$pParamHash = array();
$pParamHash['b'] =$_REQUEST['b'];
$pParamHash['find'] ='';
//TODO allow proper sort order
$pParamHash['sort_mode'] = "llc_last_modified_desc";
$max_records = $gBitSystem->getConfig( 'bitboards_rss_max_records', 10 );
$pParamHash['offset'] = 0;
$feeds = $topic->getList( $pParamHash );

// get all the data ready for the feed creator
foreach( $feeds as $feed ) {
	/*
	echo "<pre>";
	var_dump($feed);
	//*/
	$item = new FeedItem();
	$item->title = $feed['title'];
	if ($gBitUser->isRegistered()) {
		if (!empty($feed['track']['on'])&&$feed['track']['mod']) {
			$item->title = "[NEW] " .$item->title;
		}
	}
	if ($feed['th_sticky']) {
		$item->title = "[!] " .$item->title;
	}
	if ($feed['th_locked']) {
		$item->title = "[#] " .$item->title;
	}
	$item->link = 'http://'.$_SERVER['HTTP_HOST'].$feed['url'];
	$data = BitBoard::getBoard($feed['llc_content_id']);

	$item->description =  $data['data'];

	//TODO allow proper sort order
	//$item->date = ( int )$feed['event_date'];

	$item->date = ( int )$feed['llc_last_modified'];
	$item->source = 'http://'.$_SERVER['HTTP_HOST'].BIT_ROOT_URL;

	$user = new BitUser($feed['llc_user_id']);
	$user->load();

	$item->author = $user->getDisplayName();//$gBitUser->getDisplayName( FALSE, array( 'user_id' => $feed['modifier_user_id'] ) );
	$item->authorEmail = $user->mInfo['email'];

	$item->descriptionTruncSize = $gBitSystem->getConfig( 'rssfeed_truncate', 1000 );
	$item->descriptionHtmlSyndicated = FALSE;
	/*
	var_dump($item);
	echo "</pre>";
	die();
	//*/
	// pass the item on to the rss feed creator
	$rss->addItem( $item );
}

// finally we are ready to serve the data
echo $rss->saveFeed( $rss_version_name, $cacheFile );
?>
