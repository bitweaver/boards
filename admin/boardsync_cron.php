<?php

require_once( '../../bit_setup_inc.php' );
$connectionString = '{'.$gBitSystem->getConfig('boards_sync_mail_server','imap').':'.$gBitSystem->getConfig('boards_sync_mail_port','993').'/'.$gBitSystem->getConfig('boards_sync_mail_protocol','imap').'/ssl/novalidate-cert}';
if( $mbox = imap_open( $connectionString, $gBitSystem->getConfig( 'boards_sync_user' ), $gBitSystem->getConfig( 'boards_sync_password' ) ) )  {

	$MC = imap_check($mbox);

	// Fetch an overview for all messages in INBOX
	$result = imap_fetch_overview($mbox,"1:{$MC->Nmsgs}",0);
	$messageNumbers = imap_sort( $mbox, SORTDATE, 0 );
	foreach( $messageNumbers as $msgNum ) {
		$header = imap_headerinfo( $mbox, $msgNum );

		$sql = "SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `message_id`=?";
		if( !$gBitDb->getOne( $sql, array( $header->message_id ) ) ) {
$rawHeader = imap_fetchheader( $mbox, $msgNum );
//vd( $rawHeader );
$matches = array();
$toAddresses = array();
$allRecipients = '';
if( preg_match( '/To: (.*?)^[A-Z]/ms', $rawHeader, $matches ) ) {
	$allRecipients .= trim( $matches[1] ).',';
}
if( preg_match( '/Cc: (.*?)^[A-Z]/ms', $rawHeader, $matches ) ) {
	$allRecipients .= $matches[1];
}

$allSplit = split( ',', $allRecipients );
foreach( $allSplit as $s ) {
	$matches = array();
	if( strpos( $s, '<' ) !== FALSE ) {
		if( preg_match( "/(.*)<(.*)>/", trim( $s ), $matches ) ) {
			$toAddresses[] = array( 'name'=>$matches[1], 'email'=>$matches[2] );
		}
	}
}

foreach( $toAddresses AS $to ) {
	if( $boardContentId = cache_check_content_prefs( 'board_sync_list_address', $to['email'] ) ) {
		$msgStructure = imap_fetchstructure( $mbox, $msgNum );
		if( !empty( $header->in_reply_to ) ) {
			list( $replyId, $rootId ) = $gBitDb->GetRow( "SELECT `content_id`, `root_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `message_id`=?", array( $to->in_reply_to ) );
		} else {
			$replyId = $boardContentId;
			$rootId = $boardContentId;
		}
		$userInfo = board_sync_get_user( $header );
		$storeRow = array();
		$storeRow['created'] = strtotime( $header->date );	
		$storeRow['user_id'] = $userInfo['user_id'];
		$storeRow['title'] = $header->subject;
		if( $userInfo['user_id'] == ANONYMOUS_USER_ID && !empty( $header->from[0]['personal'] ) ) {
			$storeRow['anon_name'] = $header->from[0]['personal'];
		}
		$storeRow['root_id'] = $rootId;
		$storeRow['parent_id'] = $replyId;
		foreach( $msgStructure->parts as $part ) {
			if( $part->subtype == 'HTML' ) {
vd( $part );
			}
		}
vd( $storeRow );
vd( $header );
vd( $boardContentId );
vd( $to );
die;
	}
}

		}
	}

	imap_close( $mbox );

} else {
	bit_log_error( __FILE__." failed imap_open $connectionString " );
}

function board_sync_get_user( $pHeader ) {
	global $gBitUser;
	foreach( $pHeader->from AS $from ) {
		$fromEmail = $from->mailbox.'@'.$from->host;
		if( $userInfo = $gBitUser->getUserInfo( array( 'email'=>$fromEmail ) ) ) {
			return $userInfo;
		} else {
			return $gBitUser->getUserInfo( array( 'user_id'=>-1 ) );
		}
	}
	
}

function cache_check_content_prefs( $pName, $pValue ) {
	global $gBitDb, $gBitSystem;
	
	$bindVars = array( $pName, $pValue );

	return $gBitDb->getOne( "SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `pref_name`=? AND `pref_value`=?", $bindVars );
}
?>
