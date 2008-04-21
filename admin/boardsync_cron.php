<?php
global $gShellScript, $gArgs;
chdir( dirname( __FILE__ ) );
$gShellScript = TRUE;
require_once( '../../bit_setup_inc.php' );
$gBitUser->mPerms['p_users_bypass_captcha'] = TRUE; 

print '<pre>';
$connectionString = '{'.$gBitSystem->getConfig('boards_sync_mail_server','imap').':'.$gBitSystem->getConfig('boards_sync_mail_port','993').'/'.$gBitSystem->getConfig('boards_sync_mail_protocol','imap').'/ssl/novalidate-cert}';
if( $mbox = imap_open( $connectionString, $gBitSystem->getConfig( 'boards_sync_user' ), $gBitSystem->getConfig( 'boards_sync_password' ) ) )  {

	$MC = imap_check($mbox);

	// Fetch an overview for all messages in INBOX
	$result = imap_fetch_overview($mbox,"1:{$MC->Nmsgs}",0);
	if( $messageNumbers = imap_sort( $mbox, SORTDATE, 0 ) ) {
		foreach( $messageNumbers as $msgNum ) {
			$deleteMsg = TRUE;
			$header = imap_headerinfo( $mbox, $msgNum );

			$sql = "SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `message_guid`=?";
			if( !($contentId = $gBitDb->getOne( $sql, array( $header->message_id ) ) ) ) {
				$rawHeader = imap_fetchheader( $mbox, $msgNum );
				//vd( $rawHeader );
				$matches = array();
				$toAddresses = array();
				$allRecipients = '';
				if( preg_match( '/^To: (.*?)^[A-Z]/ms', $rawHeader, $matches ) ) {
					$allRecipients .= trim( $matches[1] ).',';
				}
				if( preg_match( '/^Cc: (.*?)^[A-Z]/ms', $rawHeader, $matches ) ) {
					$allRecipients .= $matches[1];
				}
	print "----$allRecipients----\n";
				$allSplit = split( ',', $allRecipients );
				foreach( $allSplit as $s ) {
					$s = trim( $s );
					$matches = array();
					if( strpos( $s, '<' ) !== FALSE ) {
						if( preg_match( "/(.*)<(.*)>/", $s, $matches ) ) {
							$toAddresses[] = array( 'name'=>$matches[1], 'email'=>$matches[2] );
						}
					} elseif( validate_email_syntax( $s ) ) {
						$toAddresses[] = array( 'email'=>$s );
						}
					}

					print( "\n---- ".date( "Y-m-d HH:mm:ss" )." -------------------------\nImporting: ".$header->message_id."\nDate: ".$header->date."\nFrom: ".$header->fromaddress."\nTo: ".$header->toaddress."\nSubject: ".$header->subject."\n" );

					foreach( $toAddresses AS $to ) {
					if( $boardContentId = cache_check_content_prefs( 'board_sync_list_address', $to['email'] ) ) {
						print "Found Board Content $boardContentId for $to[email]\n";
						$msgStructure = imap_fetchstructure( $mbox, $msgNum );
						if( !empty( $header->in_reply_to ) ) {
							if( $parent = $gBitDb->GetRow( "SELECT `content_id`, `root_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `message_guid`=?", array( $header->in_reply_to ) ) ) {
								$replyId = $parent['content_id'];
								$rootId = $parent['root_id'];
							} else {
								print ( "WARNING: Reply to unfound message: ".$header->in_reply_to ); 
								$replyId = $boardContentId;
								$rootId = $boardContentId;
							}
						} elseif( $parent = $gBitDb->GetRow( "SELECT lcom.`content_id`, lcom.`root_id` FROM `".BIT_DB_PREFIX."liberty_comments` lcom INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(lcom.`content_id`=lc.`content_id`) WHERE lc.`title`=?", array( preg_replace( '/re: /i', '', $header->subject ) ) ) ) {
							$replyId = $parent['content_id'];
							$rootId = $parent['root_id'];
						} else {
							$replyId = $boardContentId;
							$rootId = $boardContentId;
						}
						$userInfo = board_sync_get_user( $header );
						$storeRow = array();
						$storeRow['created'] = strtotime( $header->date );	
						$storeRow['last_modified'] = $storeRow['created'];
						$storeRow['user_id'] = $userInfo['user_id'];
						$storeRow['modifier_user_id'] = $userInfo['user_id'];
						$storeRow['title'] = $header->subject;
						$storeRow['message_guid'] = $header->message_id;
						if( $userInfo['user_id'] == ANONYMOUS_USER_ID && !empty( $header->from[0]->personal ) ) {
							$storeRow['anon_name'] = $header->from[0]->personal;
						}
						$storeRow['root_id'] = $rootId;
						$storeRow['parent_id'] = $replyId;

						$partHash = array();
						switch( $msgStructure->type ) {
							case '0':
								board_parse_msg_parts( $partHash, $mbox, $msgNum, $msgStructure, 1 );
								break;
							case '1':
								foreach( $msgStructure->parts as $partNum => $part ) {
									board_parse_msg_parts( $partHash, $mbox, $msgNum, $part, $partNum+1 );
								}
								break;
						}
						$plainBody = NULL;
						$htmlBody = NULL;

						foreach( array_keys( $partHash ) as $i ) {
							if( !empty( $partHash[$i]['plain'] ) ) {
								$plainBody = $partHash[$i]['plain'];
							}
							if( !empty( $partHash[$i]['html'] ) ) {
								$htmlBody = $partHash[$i]['html'];
							}
							if( !empty( $partHash[$i]['attachment'] ) ) {
								$storeRow['_files_override'][] = array( 
									'tmp_name'=> $partHash[$i]['attachment'], 
									'type'=>$gBitSystem->verifyMimeType( $partHash[$i]['attachment'] ),
									'size'=>filesize( $partHash[$i]['attachment'] ),
									'name'=>basename( $partHash[$i]['attachment'] )  );
							}
						}

						if( !empty( $htmlBody ) ) {
							$storeRow['edit'] = $htmlBody;
							$storeRow['format_guid'] = 'bithtml';
						} elseif( !empty( $plainBody ) ) {
							$storeRow['edit'] = nl2br( $plainBody );
							$storeRow['format_guid'] = 'bithtml';
						}

						// Nuke all email addresses from the body.
						$storeRow['edit'] = ereg_replace( 
								'[-!#$%&\`*+\\./0-9=?A-Z^_`a-z{|}~]+'.'@'.
								'(localhost|[-!$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
								'[-!$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+)', '', $storeRow['edit'] );

						$storeComment = new LibertyComment( NULL );
						$gBitDb->StartTrans();
						if( $storeComment->storeComment($storeRow) ) {
							$storeComment->mDb->query( "UPDATE `".BIT_DB_PREFIX."liberty_comments` SET `message_guid`=? WHERE `content_id`=?", array( $storeRow['message_guid'], $storeComment->mContentId ) );
							if($gBitSystem->isPackageActive('bitboards') && $gBitSystem->isFeatureActive('bitboards_thread_track')) {
								$topicId = substr( $storeComment->mInfo['thread_forward_sequence'], 0, 10 );
								$data = BitBoardTopic::getNotificationData( $topicId );
								foreach ($data['users'] as $login => $user) {
									if( $data['topic']->mInfo['llc_last_modified'] > $user['track_date'] && $data['topic']->mInfo['llc_last_modified']>$user['track_notify_date']) {
										$data['topic']->sendNotification($user);
									}
								}
							}
							$gBitDb->CompleteTrans();
							$deleteMsg = TRUE;
						} else {
							if( $storeComment->mErrors['store'] == 'Duplicate comment.' ) {
								$deleteMsg = TRUE;
							} else {	
								$gBitDb->RollbackTrans();
vd( $storeComment->mErrors );
die;
							}
						}
					}
				}

			} else {
				print( "WARNING: Message \"".$header->subject."\" Exists -> ".BIT_ROOT_URI."index.php?content_id=".$contentId."\n" );
				$deleteMsg = TRUE;
			}
			if( $deleteMsg && empty( $gDebug ) && empty( $gArgs['test'] ) ) {
				imap_delete( $mbox, $msgNum );
			}
		}
	}

	imap_expunge( $mbox );
	imap_close( $mbox );

} else {
	bit_log_error( __FILE__." failed imap_open $connectionString ".imap_last_error() );
}

print '</pre>';

function board_parse_msg_parts( &$pPartHash, $pMbox, $pMsgId, $pMsgPart, $pPartNum ) {

    //fetch part
    $part=imap_fetchbody( $pMbox, $pMsgId, $pPartNum);
	switch( $pMsgPart->encoding ) {
		case '3': // BASE64
			$part = base64_decode($part);
			break;
		case '4': // QUOTED-PRINTABLE
			$part = quoted_printable_decode($part);
			break;
		//0	7BIT
		//1	8BIT
		//2	BINARY
		//4	QUOTED-PRINTABLE
		//5	OTHER
	}
	switch( $pMsgPart->type ) {
		case '0':
			$pPartHash[$pPartNum][strtolower($pMsgPart->subtype)] = $part;
			break;
		default:
			// type is not text
	
			if( !preg_match( '/signature/i', $pMsgPart->subtype ) ) {		   
				//get filename of attachment if present
				$filename='';
				foreach( array( 'dparameters', 'parameters' ) as $prm ) {
					if( empty( $filename ) ) {
						// if there are any dparameters present in this part
						if( count( $pMsgPart->$prm ) > 0 ){
							foreach( $pMsgPart->$prm as $param ) {
								if( strtoupper( $param->attribute ) == 'NAME' || strtoupper( $param->attribute ) == 'FILENAME' ) {
									$filename = $param->value;
								}
							}
						}
					}
				}
				//write to disk and set pPartHash variable
				if( !empty( $filename ) ) {
vd( $pMsgPart );
					//where to write file attachments to
					srand( time() );
					$filestore = TEMP_PKG_PATH.BOARDS_PKG_NAME.'/boardsync/'.rand( 999, 999999999 ).'/'.$filename;
					mkdir_p( dirname( $filestore ) );
					$pPartHash[$pPartNum]['attachment'] = $filestore;
					$fp=fopen( $filestore, "w+" );
					fwrite( $fp, $part );
					fclose( $fp );
				}
			}
			break;
   
    }
   
    //if subparts... recurse into function and parse them too!
    if( !empty( $pMsgPart->parts ) ){
        foreach ($pMsgPart->parts as $pno=>$parr){
            board_parse_msg_parts( $pPartHash, $pMbox, $pMsgId, $parr, ( $pPartNum.'.'.( $pno + 1 ) ) );
		}
	}
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
