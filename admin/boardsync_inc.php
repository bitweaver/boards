<?php
function board_sync_run($pLog = FALSE) {
	global $gBitUser, $gBitSystem;

	$gBitUser->setPermissionOverride('p_users_bypass_captcha', TRUE);

	$connectionString = '{'.$gBitSystem->getConfig('boards_sync_mail_server','imap').':'.$gBitSystem->getConfig('boards_sync_mail_port','993').'/'.$gBitSystem->getConfig('boards_sync_mail_protocol','imap').'/ssl/novalidate-cert}';
	
	// Can we open the mailbox?
	if( $mbox = imap_open( $connectionString, $gBitSystem->getConfig( 'boards_sync_user' ), $gBitSystem->getConfig( 'boards_sync_password' ) ) )  {

		$MC = imap_check($mbox);
		
		// Fetch an overview for all messages in INBOX of mailbox has messages
		if( $MC->Nmsgs ) {
			//	  print($MC->Nmsgs);
			$result = imap_fetch_overview($mbox,"1:{$MC->Nmsgs}",0);
			if( $messageNumbers = imap_sort( $mbox, SORTDATE, 0 ) ) {
				foreach( $messageNumbers as $msgNum ) {
					if ($pLog) print "Processing Msg#: ".$msgNum."\n";
					$deleteMsg = FALSE;
					$header = imap_headerinfo( $mbox, $msgNum );
					
					// Is this a moderation message?
					if( preg_match('/.*? post from .*? requires approval/', $header->subject) ) {
						if ($pLog) print "Is Moderation Request.\n";
						// moderated messages nest the orginal message in another part
						// php imap functions dont give us easy access to part header info, so...
						// to easily get to the headers of those message we open the part as a new imap stream

						// fetch the original message
						$body = imap_fetchbody( $mbox, $msgNum, 2);
						// add a spoof time marker to the first line to make imap_open happy
						$body = "From dummy@localhost  Sat Jan  1 00:00:00 1970\n".$body;

						// write the org msg to a temp file
						$filename = 'orginal_email.eml';
						srand( time() );
						$filestore = TEMP_PKG_PATH.BOARDS_PKG_NAME.'/boardsync/'.rand( 999, 999999999 ).'/'.$filename;
						mkdir_p( dirname( $filestore ) );
						$fp=fopen( $filestore, "w+" );
						fwrite( $fp, $body );
						fclose( $fp );

						// open the temp file as an imap stream so we can use imap_headerinfo() to parse the org msg header
						$mbox2 = imap_open( $filestore, "", "" );
						$msgHeader = imap_headerinfo( $mbox2, 1 );
						// moderation validation is also in a part, extract it
						$replyBody = imap_fetchbody( $mbox, $msgNum, 3);
						$replyHeaders = board_sync_raw_headers($replyBody);
						$approveSubj = board_sync_get_header('Subject', $replyHeaders);
						$confirmCode = substr($approveSubj, strlen('confirm '));
						if ($pLog) print "Confirm code: ".$confirmCode."\n";

						$deliveredTo = board_sync_delivered_to($replyHeaders);
						$deleteMsg = board_sync_process_message($mbox, $msgNum, $msgHeader, imap_fetchstructure( $mbox, $msgNum, 2), $confirmCode, $pLog, $deliveredTo);
						// Is this a reminder message that we just skip?
					} elseif( preg_match('/[0-9]+ .*? moderator request.* waiting/', $header->subject) ) {
						if ($pLog) print "Deleting reminder.\n";
						$deleteMsg = TRUE;
					} elseif( preg_match('/Welcome to the .* mailing list/', $header->subject) ) {
						if ($pLog) print "Deleting welcome message.\n";
						$deleteMsg = TRUE;
					} else {
						// imap_headerinfo acts retarded on these emails and improperly parses the header unless we call fetchstructure first - so do it.
						// note this problem does not occure above when parsing the temp email in a moderated msg - god damn spooky
						$msgStructure = imap_fetchstructure(  $mbox, $msgNum );
						$msgHeader = imap_headerinfo( $mbox, $msgNum );
						// With multiple To: recipients it is often handy to know who the message is "Delivered-To" by the MTA
						$raw_headers = imap_fetchheader($mbox, $msgNum);
						$deliveredTo = board_sync_delivered_to($raw_headers);
						$deleteMsg = board_sync_process_message( $mbox, $msgNum, $msgHeader, $msgStructure, FALSE, $pLog, $deliveredTo);
						//					vd($deleteMsg);
					}
					if( $deleteMsg && empty( $gDebug ) && empty( $gArgs['test'] ) ) {
						//					vd("DELETE!");
						if ($pLog) print "Deleted msg $msgNum\n";
						imap_delete( $mbox, $msgNum );
					}
				}
			}
		}
		
		// final cleanup
		imap_expunge( $mbox );
		imap_close( $mbox );
		// clear everything we've written to the temp directory
		$dir = TEMP_PKG_PATH.BOARDS_PKG_NAME.'/boardsync';
		if( is_dir( $dir ) && strpos( $dir, BIT_ROOT_PATH ) === 0 ) {
			if( !unlink_r( $dir ) ) {
				bit_error_log( "Failed to clear directory: ".$dir." in boards package mailinglist synchronization." );
			}
		}
		
	} else {
		bit_error_log( __FILE__." failed imap_open $connectionString ".imap_last_error() );
	}
	
}

function board_parse_msg_parts( &$pPartHash, $pMbox, $pMsgId, $pMsgPart, $pPartNum, $pLog ) {

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

	if ($pLog) print "Msg part ".$pPartNum." type: ".$pMsgPart->subtype."\n";

	switch( $pMsgPart->type ) {
		case '0':
			// make sure text is UTF-8
			if( $pMsgPart->ifparameters ){
				foreach( $pMsgPart->parameters as $params ){
					// we trust the email source to specify the correct charset
					// Note: alternatively one might run a check to make sure the text is really utf-8, regardless of the header
					// use strtolower on the attributes since different php installs do not reconcile casing consistantly
					if( strtolower( $params->attribute ) == 'charset' && strtolower( $params->value ) != 'utf-8' ){
						if ($pLog) print( "Msg part ".$pPartNum." charset: ".$params->value."\n" ); 
						$part = @iconv($params->value, 'UTF-8', $part ); 
					}
				}
			}
			// put msg in hash
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
						if( !empty($pMsgPart->$prm) && count( $pMsgPart->$prm ) > 0 ){
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
		board_parse_msg_parts( $pPartHash, $pMbox, $pMsgId, $parr, ( $pPartNum.'.'.( $pno + 1 ) ), $pLog);
		}
	}
}

function board_sync_get_user( $pFrom ) {
	global $gBitUser;

	if( preg_match_all('/[^<\s]+@[^>\s]+/', $pFrom, $matches) ) {
		foreach( $matches[0] as $email ) {
			$ret = $gBitUser->getUserInfo( array( 'email'=>$email ) );
			if( !empty($ret) ) {
				return $ret;
			}
		}
	}

	return $gBitUser->getUserInfo( array( 'user_id'=>-1 ) );
}

function cache_check_content_prefs( $pName, $pValue, $pLower = FALSE ) {
	global $gBitDb, $gBitSystem;
	static $prefs;

	if( empty($prefs[$pLower][$pName]) ) {		
		$bindVars = array( $pName );
		$prefs[$pLower][$pName] = $gBitDb->getAssoc( "SELECT " .
						    ($pLower ? 'LOWER(`pref_value`)' : '`pref_value`').
						    ", `content_id` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `pref_name`=?", $bindVars );
	}

	if( !empty($prefs[$pLower][$pName][$pValue]) ) {
		return $prefs[$pLower][$pName][$pValue];
	}

	return NULL;
}

/**
 * $pMsgHeader is a imap_headerinfo generated array
 **/
function board_sync_process_message( $pMbox, $pMsgNum, $pMsgHeader, $pMsgStructure, $pModerate = FALSE , $pLog=FALSE, $pDeliveredTo=NULL) {
	global $gBitSystem, $gBitDb;
	// vd( $pMsgHeader );

	// Collect a bit of header information
	$message_id = board_sync_get_headerinfo( $pMsgHeader, 'message_id' );
	// @TODO comment or clean up, not sure why this is here -wjames5
	if( empty($message_id) ) {
		$message_id = board_sync_get_headerinfo( $pMsgHeader, 'message_id' );
	}	
	$subject = board_sync_get_headerinfo( $pMsgHeader, 'Subject' );

	if( empty( $message_id ) ){
		bit_error_log( "Email sync for message: ".$subject." failed: No Message Id in mail header." );
	}else{
		if ($pLog) print("Processing: ".$message_id."\n");
		if ($pLog) print("  Subject: ".$subject."\n");


		$matches = array();
		$toAddresses = array();
		$allRecipients = "";
		if (empty($pDeliveredTo)) {
			if( isset( $pMsgHeader->toaddress ) ){
				$allRecipients .= $pMsgHeader->toaddress;
				if ($pLog) print ("  To addresses: " . $pMsgHeader->toaddress . "\n");
			}
			if( isset( $pMsgHeader->ccaddress ) ){
				$allRecipients .= (( $allRecipients != "" )?",":"") . $pMsgHeader->ccaddress;
				if ($pLog) print ("  CC addresses: " . $pMsgHeader->ccaddress . "\n");
			}
			
			if ($pLog) print ("  All Recipients: ". $allRecipients ."\n");
			$allSplit = split( ',', $allRecipients );
			foreach( $allSplit as $s ) {
				$s = trim( $s );
				$matches = array();
				if( strpos( $s, '<' ) !== FALSE ) {
					if( preg_match( "/\s*(.*)\s*<\s*(.*)\s*>/", $s, $matches ) ) {
						$toAddresses[] = array( 'name'=>$matches[1], 'email'=>$matches[2] );
					} elseif( preg_match('/<\s*(.*)\s*>\s*(.*)\s*/', $s, $matches) ) {
						$toAddresses[] = array( 'email'=>$matches[1], 'name'=>$matches[2] );
					}
				} elseif( validate_email_syntax( $s ) ) {
					$toAddresses[] = array( 'email'=>$s );
				}
			}
		} else {
			foreach ($pDeliveredTo as $address) {
				$toAddresses[] = array('email' => $address);
			}
		}
		if ($pLog) print_r($toAddresses);
		
		$date = board_sync_get_headerinfo($pMsgHeader, 'Date');
		$from = board_sync_get_headerinfo($pMsgHeader, 'from');
		$fromaddress = $from[0]->mailbox."@".$from[0]->host;
		// personal is not always defined.
		if (isset($from[0]->personal)) {
			$personal = ucwords($from[0]->personal);
		} else {
			$personal = null;
		}
		$in_reply_to = board_sync_get_headerinfo($pMsgHeader, 'in_reply_to');

		if ($pLog) print( "\n---- ".date( "Y-m-d HH:mm:ss" )." -------------------------\nImporting: ".$message_id."\nDate: ".$date."\nFrom: ".$fromaddress."\nTo: ".$allRecipients."\nSubject: ".$subject."\nIn Reply To: ".$in_reply_to."\nName: ".$personal.(is_array($pDeliveredTo) ? "\nDelivered-To:".implode(", ", $pDeliveredTo) : '')."\n");

		foreach( $toAddresses AS $to ) {
			if ($pLog) print( "  Processing email: " . strtolower($to['email']) . "\n");
			// get a board match for the email address
			if( $boardContentId = cache_check_content_prefs( 'board_sync_list_address', strtolower($to['email']), TRUE ) ) {
				if ($pLog) print "Found Board Content $boardContentId for $to[email]\n";

				// Do we already have this message in this board?
				$contentId = NULL;
				if( $message_id != NULL ) {
					$sql = "SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `message_guid`=? AND `root_id`=?";
					$contentId = $gBitDb->getOne( $sql, array( $message_id, $boardContentId ) );
				}
				if( empty($contentId) ) {
					if( !empty( $in_reply_to ) ) {
						if( $parent = $gBitDb->GetRow( "SELECT `content_id`, `root_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `message_guid`=?", array( $in_reply_to ) ) ) {
							$replyId = $parent['content_id'];
							$rootId = $parent['root_id'];
						} else {
							if ($pLog) print ( "WARNING: Reply to unfound message: ".$in_reply_to );
							$replyId = $boardContentId;
							$rootId = $boardContentId;
						}
						// if no reply to message guid then match on title - this looks dangerous as titles could easily be duplicated -wjames
					} elseif( $parent = $gBitDb->GetRow( "SELECT lcom.`content_id`, lcom.`root_id` FROM `".BIT_DB_PREFIX."liberty_comments` lcom INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(lcom.`content_id`=lc.`content_id`) WHERE lc.`title`=?", array( preg_replace( '/re: /i', '', $subject ) ) ) ) {
						$replyId = $parent['content_id'];
						$rootId = $parent['root_id'];
						// attach to board as first level comment e.g. new topic
					} else {
						$replyId = $boardContentId;
						$rootId = $boardContentId;
					}
					$userInfo = board_sync_get_user( $fromaddress );
					// prep the storage hash
					$storeRow = array();
					$storeRow['created'] = strtotime( $date );
					$storeRow['last_modified'] = $storeRow['created'];
					$storeRow['user_id'] = $userInfo['user_id'];
					$storeRow['modifier_user_id'] = $userInfo['user_id'];
					$storeRow['title'] = $subject;
					$storeRow['message_guid'] = $message_id;
					if( $userInfo['user_id'] == ANONYMOUS_USER_ID && !empty( $personal ) ) {
						$storeRow['anon_name'] = $personal;
					}
					$storeRow['root_id'] = $rootId;
					$storeRow['parent_id'] = $replyId;
					
					$partHash = array();
					
					switch( $pMsgStructure->type ) {
					case '0':
						if ($pLog) print( "Structure Type: text\n" );
						board_parse_msg_parts( $partHash, $pMbox, $pMsgNum, $pMsgStructure, 1, $pLog );
						break;
					case '1':
						if ($pLog) print( "Structure Type: multipart\n" );
						if ($pModerate) {
							$prefix = '2.';
						}
						else {
							$prefix = '';
						}
						foreach( $pMsgStructure->parts as $partNum => $part ) {
							board_parse_msg_parts( $partHash, $pMbox, $pMsgNum, $part, $prefix.($partNum+1), $pLog );
						}
						break;
					}
					$plainBody = "";
					$htmlBody = "";
					
					foreach( array_keys( $partHash ) as $i ) {
						if( !empty( $partHash[$i]['plain'] ) ) {
							$plainBody .= $partHash[$i]['plain'];
						}
						if( !empty( $partHash[$i]['html'] ) ) {
							$htmlBody .= $partHash[$i]['html'];
						}
						if( !empty( $partHash[$i]['attachment'] ) ) {
							$storeRow['_files_override'][] = array(
											       'tmp_name'=> $partHash[$i]['attachment'],
											       'type'=>$gBitSystem->verifyMimeType( $partHash[$i]['attachment'] ),
											       'size'=>filesize( $partHash[$i]['attachment'] ),
											       'name'=>basename( $partHash[$i]['attachment'] ),
											       'user_id'=>$userInfo['user_id'] );
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
					if( !empty($storeRow['edit']) ) {
						$storeRow['edit'] = ereg_replace(
										 '[-!#$%&\`*+\\./0-9=?A-Z^_`a-z{|}~]+'.'@'.
										 '(localhost|[-!$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
										 '[-!$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+)', '', $storeRow['edit'] );
					}
					
					// We trust the user from this source
					// and count on moderation to handle links
					global $gBitUser;
					$gBitUser->setPermissionOverride('p_liberty_trusted_editor', true);

					
					// Check to add attachments 
					
					// NOTE: we temporarily change the gBitUser here!
					// This is so we can run a proper content permissions check
					// for attachment permission against the parent
					// board object. This is sort of a hack to deal 
					// with the fact that LibertyContent does not have a
					// means to check the permissions of any user except gBitUser -wjames5
					
					// Important store a reference so we can switch back when we are done
					$gBitUserOrg = $gBitUser;
					
					// Load the message sending user
					if( $userInfo['user_id'] != ANONYMOUS_USER_ID ) {
						$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
						$newBitUser = new $userClass( $userInfo['user_id'] );
						$newBitUser->load( TRUE );
					}
					if( !empty( $newBitUser ) && $newBitUser->isValid() ){
						// flip gBitUser to our message sender
						$gBitUser = $newBitUser;
					}
				
					// Load the parent board
					$board = new BitBoard( NULL, $boardContentId );
					$board->load();
				
					// Check the permission for the user on the board
					if( $gBitSystem->isFeatureActive( 'comments_allow_attachments' ) && $board->hasUserPermission( 'p_liberty_attach_attachments' ) ){ 
						// note we grant the permission to the anonymous user which will become gBitUser once again
						$gBitUserOrg->setPermissionOverride('p_liberty_attach_attachments', true);
					};
				
					// Clear the reference to this board so we dont mistakenly use it later
					unset( $board );
				
					// Important: switch gBitUser back!
					$gBitUser = $gBitUserOrg;
				
					// End check to add attachments to comments to the parent board
				
					// Check for an empty body
					// Duplicate subject if we have it
					if (empty($storeRow['edit'])) {
						if (!empty($storeRow['title'])) {
							$storeRow['edit'] = $storeRow['title'];
						}
						else {
							$storeRow['edit'] = ".";
						}
					}
				
				
					$storeComment = new LibertyComment( NULL );
					$gBitDb->StartTrans();
					if( $storeComment->storeComment($storeRow) ) {
						// undo the attachment permission
						$gBitUser->setPermissionOverride('p_liberty_attach_attachments', false);
					
						// set moderation approval
						if( !$pModerate && $gBitSystem->isPackageActive('moderation') && $gBitSystem->isPackageActive('modcomments') ) {
							global $gModerationSystem, $gBitUser;
							$moderation = $gModerationSystem->getModeration(NULL, $storeComment->mContentId);
							if( !empty($moderation) ) {
								// Allow to moderate
								$gBitUser->setPermissionOverride('p_admin', TRUE);
								$gModerationSystem->setModerationReply($moderation['moderation_id'], MODERATION_APPROVED);
								$gBitUser->setPermissionOverride('p_admin', FALSE);
							}
						}
					
						if( !empty( $storeRow['message_guid'] ) ){
							// map the message guid to the comment
							$storeComment->mDb->query( "UPDATE `".BIT_DB_PREFIX."liberty_comments` SET `message_guid`=? WHERE `content_id`=?", array( $storeRow['message_guid'], $storeComment->mContentId ) );
							
							// Store the confirm code
							if( $pModerate ) {
								$storeComment->storePreference('board_confirm_code', $pModerate);
							}
						
							// done
							$gBitDb->CompleteTrans();
							return TRUE;
						}else{
							bit_error_log( "Email sync error: Message Id not set. You shouldn't have even gotten this far." );
							$gBitDb->RollbackTrans();
							return FALSE;
						}
					} else {
						if( count( $storeComment->mErrors ) == 1 && !empty( $storeComment->mErrors['store'] ) && $storeComment->mErrors['store'] == 'Duplicate comment.' ) {
							return TRUE;
						} else {
							foreach( $storeComment->mErrors as $error ){
								bit_error_log( $error );
							}
							$gBitDb->RollbackTrans();
							return FALSE;
						}
					}

				} else {
					if ($pLog) print "Message Exists: $contentId : $boardContentId : $message_id : $pModerate\n";
					// If this isn't a moderation message
					if( $pModerate === FALSE ) {
						// If the message exists it must have been approved via some
						// moderation mechanism, so make sure it is available
						if( $gBitSystem->isPackageActive('moderation') && $gBitSystem->isPackageActive('modcomments') ) {
							global $gModerationSystem, $gBitUser;
							$storeComment = new LibertyComment( NULL, $contentId );
							$storeComment->loadComment();
							if ($storeComment->mInfo['content_status_id'] > 0) {
								if ($pLog) print "Already approved: $contentId\n";
							} else {
								$moderation = $gModerationSystem->getModeration(NULL, $contentId);
								//				vd($moderation);
								if( !empty($moderation) ) {
									$gBitUser->setPermissionOverride('p_admin', TRUE);
									if ($pLog) print( "Setting approved: $contentId\n" );
									$gModerationSystem->setModerationReply($moderation['moderation_id'], MODERATION_APPROVED);
									$gBitUser->setPermissionOverride('p_admin', FALSE);
									if ($pLog) print "Done";
								} else {
									if ($pLog) print "ERROR: Unable to find moderation to approve for: $contentId";
								}
							}
						}
					} else {
						// Store the approve code;
						if ($pLog) print "Storing approval code: " . $contentId . ":" . $pModerate . "\n";
						$storeComment = new LibertyComment( NULL, $contentId );
						$storeComment->storePreference('board_confirm_code', $pModerate);
					}
					return TRUE;
				}
			} else {
				if ($pLog) print "No Board match found for $to[email]\n";
			}
		}
	}
	return FALSE;
}

function board_sync_raw_headers($body) {
	$matches = preg_split('/^\s*$/ms', $body, 2);
	return $matches[0];
}

function board_sync_get_header($header, $body) {
	$ret = NULL;
	preg_match( '/^'.$header.':\s*(.*?)\s*$/m', $body, $matches);
	if (!empty($matches[1])) {
		$ret = $matches[1];
	}
	return $ret;
}

// $header is imap_headerinfo array
function board_sync_get_headerinfo( $header, $key ){
	$ret = NULL;
	if( isset( $header->$key ) ){
		$ret = $header->$key;
	}
	return $ret;
}

function board_sync_delivered_to( $raw_headers ) {
	$ret = null;
	if (isset($raw_headers) && 
	    preg_match_all("/Delivered-To:\s*(.*)\s*/", $raw_headers, $deliveredTo) > 0) {
		$ret = array();
		foreach ($deliveredTo[1] as $address) {
			// Make sure the Delivered-To: address is valid.
			if (validate_email_syntax( $address ) ) {
				$ret[] = strtolower(trim($address));
			}
		}
	}
	return $ret;
}

function is_utf8($string) {
    // From http://w3.org/International/questions/qa-forms-utf-8.html
    return preg_match('%^(?:
          [\x09\x0A\x0D\x20-\x7E]            # ASCII
        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
    )*$%xs', $string);
}
