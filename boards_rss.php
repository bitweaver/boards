<?php
/**
 * @version $Header$
 * @package boards
 * @subpackage functions
 */

/**
 * Initialization
 */
require_once( "../kernel/setup_inc.php" );

$gBitSystem->verifyPackage( 'boards' );
$gBitSystem->verifyPackage( 'rss' );

require_once( RSS_PKG_PATH."rss_inc.php" );

// Load up the board or topic
require_once( BOARDS_PKG_PATH.'lookup_inc.php' );

// access check
if( !empty( $_REQUEST['t'] ) || !empty($_REQUEST['b'] ) ){
	if( $gContent->isValid() ){
		$gContent->load();
	}
	else{
		$gBitSystem->fatalError(tra("Unknown discussion"), NULL, NULL, HttpStatusCodes::HTTP_NOT_FOUND );
	}
} 
$gContent->verifyViewPermission();

// check if we want to use the cache file
// HTTP_HOST is needed beacuse people subscribe to RSS via different URLs (docs.bw.o and www.bw.o for example)
// cached versions of other URLs will double posts 
// $cacheFile = TEMP_PKG_PATH.RSS_PKG_NAME.'/'.BOARDS_PKG_NAME.'/'.$_SERVER['HTTP_HOST']."_".$cacheFileTail;
$cacheFile = TEMP_PKG_PATH.RSS_PKG_NAME.'/'.BOARDS_PKG_NAME.'/'.$_SERVER['HTTP_HOST'];
// BitTopic acts strange and does not set mContentTypeGuid
switch( $gContent->getField('content_type_guid') ){
	case 'bitcomment':
		$cacheFile .= '_comment'.$_REQUEST['t'];
		break;
	case 'bitboard':
		if( $gContent->isValid() ){
			$cacheFile .= '_board'.$_REQUEST['b'];
		}
		break;
	default:
		break;
}
$cacheFile.="_".$cacheFileTail;
$rss->useCached( $rss_version_name, $cacheFile, $gBitSystem->getConfig( 'rssfeed_cache_time' ));

$title = tra("Recent Discussions");
$description = tra("All recent forum discussions on ".$gBitSystem->getConfig( 'site_title' ) );
if( $gContent->isValid() ){
	$gContent->parseData();
	$title = $gContent->getField( 'title' )." Feed";
	$description = $gContent->getField( 'parsed_data' );
}
$rss->title = $title; 
$rss->description = $description; 
$rss->link =  'http://'.$_SERVER['HTTP_HOST'].$gContent->getDisplayUrl();


// get all topics of a board or all recent topics in general
switch( $gContent->getField('content_type_guid') ){
	case 'bitcomment':
		// need to use post class to get list of comments 
		$gComment = new BitBoardPost($_REQUEST['t']);
		// pass in a reference to the root object so that we can do proper permissions checks
		$gComment->mRootObj = $gContent;
		$feeds = $gComment->getComments( $gContent->mContentId, $gBitSystem->getConfig( 'boards_rss_max_records', 10 ), 0, 'commentDate_desc', 'flat' );
		break;
	case 'bitboard':
	default:
		$topic = new BitBoardTopic();
		$pParamHash = array();
		if( !empty( $_REQUEST['b'] ) ) {
			$pParamHash['b'] = $_REQUEST['b'];
		}
		$pParamHash['find'] ='';
		$pParamHash['sort_mode'] = "llc_last_modified_desc";
		$pParamHash['max_records'] = $gBitSystem->getConfig( 'boards_rss_max_records', 10 );
		$pParamHash['offset'] = 0;
		$feeds = $topic->getList( $pParamHash );
		break;
}

// get all the data ready for the feed creator
foreach( $feeds as $feed ) {
	/*
	echo "<pre>";
	var_dump($feed);
	//*/
	$item = new FeedItem();
	$item->title = $feed['title'];
	$item->source = 'http://'.$_SERVER['HTTP_HOST'].BIT_ROOT_URL;

	switch( $gContent->getField('content_type_guid') ){
		case 'bitcomment':
			// topic specific 
			$item->link = 'http://'.$_SERVER['HTTP_HOST'].BIT_ROOT_URL.'index.php?content_id='.$feed['content_id']; //comment paths are tricky, but work automagically through the front door
			$item->description =  $feed['parsed_data'];
			$item->date = ( int )$feed['last_modified'];
			$user = new BitUser($feed['user_id']);
			break;
		case 'bitboard':
		default:
			// board specific
			if ($gBitUser->isRegistered()) {
				if (!empty($feed['track']['on'])&&$feed['track']['mod']) {
					$item->title = "[NEW] " .$item->title;
				}
			}
			if( !empty( $feed['th_sticky'] ) ) {
				$item->title = "[!] " .$item->title;
			}
			if( !empty( $feed['th_locked'] ) ) {
				$item->title = "[#] " .$item->title;
			}
			$item->link = 'http://'.$_SERVER['HTTP_HOST'].$feed['url'];
			$data = BitBoard::getBoard($feed['llc_content_id']);
			$item->description =  $data['data'];
			//TODO allow proper sort order
			//$item->date = ( int )$feed['event_date'];
			$item->date = ( int )$feed['llc_last_modified'];
			$user = new BitUser($feed['llc_user_id']);
			break;
	}

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
