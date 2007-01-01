<?php
/**
* $Header: /cvsroot/bitweaver/_bit_boards/BitBoardTopic.php,v 1.18 2007/01/01 01:29:00 spiderr Exp $
* $Id: BitBoardTopic.php,v 1.18 2007/01/01 01:29:00 spiderr Exp $
*/

/**
* Messageboards class to illustrate best practices when creating a new bitweaver package that
* builds on core bitweaver functionality, such as the Liberty CMS engine
*
* @date created 2004/8/15
* @author spider <spider@steelsun.com>
* @version $Revision: 1.18 $ $Date: 2007/01/01 01:29:00 $ $Author: spiderr $
* @class BitBoardTopic
*/

require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );

/**
* This is used to uniquely identify the object
*/
define( 'BITBOARDTOPIC_CONTENT_TYPE_GUID', 'bitboardtopic' );

class BitBoardTopic extends LibertyAttachable {
	/**
	* Primary key for our mythical Messageboards class object & table
	* @public
	*/
	var $mRootId;

	/**
	* During initialisation, be sure to call our base constructors
	**/
	function BitBoardTopic( $pRootId=NULL ) {
		LibertyAttachable::LibertyAttachable();
		$this->mRootId = $pRootId;
	}

	/**
	* Load the data from the database
	* @param pParamHash be sure to pass by reference in case we need to make modifcations to the hash
	**/
	function load() {
		global $gBitUser, $gBitSystem;
		if( $this->verifyId( $this->mRootId ) || $this->verifyId( $this->mContentId ) ) {
			// LibertyAttachable::load()assumes you have joined already, and will not execute any sql!
			// This is a significant performance optimization
			$lookupColumn = $this->verifyId( $this->mRootId ) ? 'lcom.`comment_id`' : 'lc.`content_id`';
			$bindVars = array();
			$selectSql = $joinSql = $whereSql = '';
			array_push( $bindVars, $lookupId = @BitBase::verifyId( $this->mRootId ) ? $this->mRootId : $this->mContentId );
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			if (!($gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit'))) {
				//$whereSql .= " AND ((first.`approved` = 1) OR (flc.`user_id` >= 0))";
			}

			BitBoardTopic::loadTrack($selectSql, $joinSql);

			$BIT_DB_PREFIX = BIT_DB_PREFIX;
			$query ="
SELECT
	lc.`user_id` AS flc_user_id,
	lc.`created` AS flc_created,
	lc.`last_modified` AS flc_last_modified,
	lc.`title` AS title,
	lc.`content_id` AS flc_content_id,

	COALESCE(post.`approved`,0) AS first_approved,
	lcom.`anon_name`,

	th.`parent_id` AS th_first_id,
	COALESCE(th.`is_locked`,0) AS th_is_locked,
	COALESCE(th.`is_moved`,0) AS th_is_moved,
	COALESCE(th.`is_sticky`,0) AS th_is_sticky,

	lcom.`comment_id` AS th_thread_id,
	lcom.`root_id` AS th_root_id,
	lcom.`root_id` AS content_id,
	lc.`content_type_guid` AS content_type_guid,

	map.`board_content_id` AS board_content_id

	$selectSql
FROM `${BIT_DB_PREFIX}liberty_comments` lcom
	INNER JOIN `${BIT_DB_PREFIX}liberty_content` lc ON( lc.`content_id` = lcom.`content_id` )
	INNER JOIN `${BIT_DB_PREFIX}boards_map` map ON (map.`topic_content_id`=lcom.`root_id` )
	LEFT JOIN `${BIT_DB_PREFIX}boards_topics` th ON (th.`parent_id`=lcom.`comment_id`)
	LEFT JOIN `${BIT_DB_PREFIX}boards_post` post ON(post.`comment_id`=lcom.`comment_id`)
	$joinSql
WHERE
	lcom.`root_id`=lcom.`parent_id` AND	$lookupColumn=?
	$whereSql";
			$result = $this->mDb->query( $query, $bindVars );
			if( $result && $result->numRows() ) {
				$this->mInfo = $result->fields;
				$llc_data = BitBoardTopic::getLastPost($this->mInfo);
				$this->mInfo = array_merge($this->mInfo,$llc_data);
				$this->mRootId = $result->fields['th_thread_id'];
				BitBoardTopic::track($this->mInfo);
				$this->mInfo['display_url'] = $this->getDisplayUrl();
				if (empty($this->mInfo['anon_name'])) $this->mInfo['anon_name'] = "Anonymous";

				LibertyAttachable::load();
			}
		}
		return( count( $this->mInfo ) );
	}

	/**
	* This function removes a bitboard entry
	**/
	function expunge() {
		$gBitSystem->verifyPermission('p_bitboards_edit');
		$this->mDb->StartTrans();
		$comment =  new LibertyComment($this->mRootId);
		$comment->expungeComments();
		$query = "DELETE FROM `".BIT_DB_PREFIX."boards_topics` WHERE `thread_id` = ?";
		$result = $this->mDb->query( $query, array( $this->mRootId ) );
		$this->mDb->CompleteTrans();
		return $ret;
	}

	function verify( &$pParamHash ) {
		if( isset( $pParamHash['is_locked'] ) ) {
			if( !is_numeric( $pParamHash['is_locked'] ) || $pParamHash['is_locked'] > 1 || $pParamHash['is_locked'] < 0 ) {
				$this->mErrors[]=("Invalid topic state");
			} else {
				$pParamHash['topic_store']['is_locked'] = $pParamHash['is_locked'];
			}
		}
		if( isset( $pParamHash['is_moved'] ) ) {
			if( !is_numeric( $pParamHash['is_moved'] ) || $pParamHash['is_moved'] > 1 || $pParamHash['is_moved'] < 0 ) {
				$this->mErrors[]=("Invalid move state");
			} else {
				$pParamHash['topic_store']['is_moved'] = $pParamHash['is_moved'];
			}
		}
		if( !empty( $pParamHash['is_sticky'] ) ) {
			if( !is_numeric( $pParamHash['is_sticky'] ) || $pParamHash['is_sticky'] > 1 || $pParamHash['is_sticky'] < 0 ) {
				$this->mErrors[]=("Invalid sticky state");
			} else {
				$pParamHash['topic_store']['is_sticky'] = $pParamHash['is_sticky'];
			}
		}
		if( !empty( $pParamHash['migrate_topic_id'] ) ) {
			$pParamHash['topic_store']['migrate_topic_id'] = $pParamHash['migrate_topic_id'];
		}

		return( count( $this->mErrors ) == 0 && !empty( $pParamHash['topic_store'] ) );
	}

	/**
	* This function stickies a topic
	**/
	function store( &$pParamHash ) {
		global $gBitSystem;
		$ret = FALSE;
		if( $this->mRootId && $this->verify( $pParamHash ) ) {
			//$gBitSystem->verifyPermission('p_bitboards_edit');
			//$pParamHash = (($pParamHash + 1)%2);
			$query_sel = "SELECT * FROM `".BIT_DB_PREFIX."boards_topics` WHERE `parent_id` = ?";
			$isStored = $this->mDb->getOne( $query_sel, array( $this->mRootId ) );
			if( $isStored ) {
				$result = $this->mDb->associateUpdate( 'boards_topics', $pParamHash['topic_store'], array( 'parent_id' => $this->mRootId ) );
			} else {
				$pParamHash['topic_store']['parent_id'] = $this->mRootId;
				$result = $this->mDb->associateInsert( 'boards_topics', $pParamHash['topic_store'] );
			}
			$ret = TRUE;
		}
		return $ret;
	}	

	/**
	* This function moves a topic to a new messageboard
	**/
	function moveTo($board_id) {
		$ret = FALSE;
		$this->mDb->StartTrans();
		$lcom = new LibertyComment();
		$lcom_hash['data']="The comments from: {$this->mInfo['title']} ({$this->mRootId}) have been is_moved to $board_id";
		$lcom_hash['title']=$this->mInfo['title'];
		$lcom_hash['parent_id']=$this->mInfo['th_root_id'];
		$lcom_hash['root_id']=$this->mInfo['th_root_id'];
		$lcom_hash['created']=$this->mInfo['flc_created'];
		$lcom_hash['last_modified']=$this->mInfo['flc_last_modified'];
		$lcom->storeComment($lcom_hash);
		$lcom->mCommentId;
		$data = array();
		$data['parent_id']=$lcom->mCommentId;
		$data['is_moved']=$this->mRootId;
		$this->mDb->associateInsert( BIT_DB_PREFIX."boards_topics", $data );
		$query = "UPDATE `".BIT_DB_PREFIX."liberty_comments`
			SET
				`root_id` = $board_id,
				`parent_id` = $board_id
			WHERE
				`thread_forward_sequence` LIKE '".sprintf("%09d.", $this->mRootId)."%'
				AND `root_id`=`parent_id`
				";
		$result = $this->mDb->query( $query );
		$query = "UPDATE `".BIT_DB_PREFIX."liberty_comments`
			SET
				`root_id` = $board_id
			WHERE
				`thread_forward_sequence` LIKE '".sprintf("%09d.", $this->mRootId)."%'";
		$result = $this->mDb->query( $query );
		$this->mDb->CompleteTrans();
		$ret = TRUE;

		return $ret;
	}

	/**
	* This function generates a list of records from the liberty_content database for use in a list page
	**/
	function getList( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		$BIT_DB_PREFIX = BIT_DB_PREFIX;
		// this makes sure parameters used later on are set
		LibertyAttachable::prepGetList( $pParamHash );

		$selectSql = $joinSql = $whereSql = '';
		$bindVars = array();
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		// this will set $find, $sort_mode, $max_records and $offset
		extract( $pParamHash );

		if(empty($find)) {
		} elseif( is_array( $find ) ) {
			// you can use an array of pages
			$whereSql .= " AND flc.`title` IN( ".implode( ',',array_fill( 0,count( $find ),'?' ) )." )";
			$bindVars = array_merge ( $bindVars, $find );
		} elseif( is_string( $find ) ) {
			// or a string
			$whereSql .= " AND UPPER( lc.`title` ) LIKE '%". strtoupper( $find ). "%'";
		}

		if(!empty($pParamHash['b'])) {
			$joinSql .= " INNER JOIN `${BIT_DB_PREFIX}boards_map` map ON (map.`topic_content_id` = lcom.`root_id`)";
			$joinSql .= " INNER JOIN `${BIT_DB_PREFIX}boards` b ON (b.`content_id` = map.`board_content_id`)";
			$whereSql .= " AND b.`board_id` = ". $pParamHash['b'] ;
		}

		BitBoardTopic::loadTrack($selectSql,$joinSql);

		if ( $this->mDb->mType == 'firebird' ) {
			$substrSql = "SUBSTRING(s_lcom.`thread_forward_sequence` FROM 1 FOR 10) LIKE SUBSTRING(lcom.`thread_forward_sequence` FROM 1 FOR 10)";
		} else {
			$substrSql = "SUBSTRING(s_lcom.`thread_forward_sequence`, 1, 10) LIKE SUBSTRING(lcom.`thread_forward_sequence`, 1, 10)";
		}

		if ($gBitSystem->isFeatureActive('bitboards_post_anon_moderation') && !($gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit'))) {
			$whereSql .= " AND ((post.`approved` = 1) OR (lc.`user_id` >= 0))";
		}
		if ($gBitSystem->isFeatureActive('bitboards_post_anon_moderation') && ($gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit'))) {
			$selectSql .= ", ( SELECT COUNT(*)
			FROM `${BIT_DB_PREFIX}liberty_comments` AS s_lcom
			INNER JOIN `".BIT_DB_PREFIX."liberty_content` s_lc ON (s_lcom.`content_id` = s_lc.`content_id`)
			LEFT JOIN  `${BIT_DB_PREFIX}boards_post` s ON( s_lcom.`comment_id` = s.`comment_id` )
			WHERE (".$substrSql.") AND ((s_lc.`user_id` < 0) AND (s.`approved` = 0 OR s.`approved` IS NULL))
			) AS unreg";
		} else {
			$selectSql .= ", 0 AS unreg";
		}

		$sort_sql = "flc.".$this->mDb->convert_sortmode( $sort_mode );

		$query = "SELECT
	lc.`user_id` AS flc_user_id,
	lc.`created` AS flc_created,
	lc.`last_modified` AS flc_last_modified,
	lc.`title` AS title,
	lc.`content_id` AS flc_content_id,

	COALESCE(post.`approved`,0) AS first_approved,
	lcom.`anon_name`,

	th.`parent_id` AS th_first_id,
	COALESCE(th.`is_locked`,0) AS th_is_locked,
	COALESCE(th.`is_moved`,0) AS th_is_moved,
	COALESCE(th.`is_sticky`,0) AS th_is_sticky,

	lcom.`comment_id` AS th_thread_id,
	lcom.`root_id` AS th_root_id,

	lcom.`root_id` AS content_id,
	lc.`content_type_guid` AS content_type_guid,

	(
		SELECT COUNT(*)
		FROM `".BIT_DB_PREFIX."liberty_comments` s_lcom
		INNER JOIN `".BIT_DB_PREFIX."liberty_content` s_lc ON (s_lcom.`content_id` = s_lc.`content_id`)
	    WHERE (".$substrSql.")
	) AS post_count

	$selectSql
		FROM `${BIT_DB_PREFIX}liberty_comments` lcom
		INNER JOIN `${BIT_DB_PREFIX}liberty_content` lc ON( lc.`content_id` = lcom.`content_id` )
		LEFT JOIN `${BIT_DB_PREFIX}boards_topics` th ON (th.`parent_id`=lcom.`comment_id`)
		LEFT JOIN `${BIT_DB_PREFIX}boards_post` post ON (post.`comment_id` = lcom.`comment_id`)
		$joinSql
WHERE
	lcom.`root_id`=lcom.`parent_id`
	$whereSql
ORDER BY
	11 DESC,
	10 ASC,
	lc.created DESC
";
		$query_cant  = "SELECT count(*)
FROM `${BIT_DB_PREFIX}liberty_comments` lcom
INNER JOIN `${BIT_DB_PREFIX}liberty_content` lc ON( lc.`content_id` = lcom.`content_id` )
LEFT JOIN `${BIT_DB_PREFIX}boards_topics` th ON (th.`parent_id`=lcom.`comment_id`)
LEFT JOIN `${BIT_DB_PREFIX}boards_post` post ON (post.`comment_id` = lcom.`comment_id`)
$joinSql
WHERE
	lcom.`root_id`=lcom.`parent_id`
	$whereSql";
		$result = $this->mDb->query( $query, $bindVars, $max_records, $offset );
		$ret = array();
		while( $res = $result->fetchRow() ) {
			if (empty($res['anon_name'])) $res['anon_name'] = "Anonymous";
			if ($res['th_is_moved']>0) {
				$res['url']=BITBOARDS_PKG_URL."index.php?t=".$res['th_is_moved'];
			} else {
				$res['url']=BITBOARDS_PKG_URL."index.php?t=".$res['th_thread_id'];
			}
			$llc_data = BitBoardTopic::getLastPost($res);
			$res = array_merge($res,$llc_data);
			BitBoardTopic::track($res);
			$res['flip']=BitBoardTopic::getFlipFlop($res);
			if (empty($res['title'])) {
				$res['title']="[Thread ".$res['th_thread_id']."]";
			}
			$ret[] = $res;
		}
		$pParamHash["cant"] = $this->mDb->getOne( $query_cant, null );
		// add all pagination info to pParamHash
		LibertyAttachable::postGetList( $pParamHash );
		return $ret;
	}

	function getLastPost($data) {
		global $gBitSystem;
		if ( $this->mDb->mType == 'firebird' ) {
			$substrSql = "SUBSTRING(lcom.`thread_forward_sequence` FROM 1 FOR 10)";
		} else {
			$substrSql = "SUBSTRING(lcom.`thread_forward_sequence`, 1, 10)";
		}
		$whereSql = '';
		if ($gBitSystem->isFeatureActive('bitboards_post_anon_moderation')) {
			$whereSql = " AND ((post.`approved` = 1) OR (lc.`user_id` >= 0))";
		}
		$BIT_DB_PREFIX = BIT_DB_PREFIX;
		$query="SELECT lc.`last_modified` AS llc_last_modified, lc.`user_id` AS llc_user_id, lc.`content_id` AS llc_content_id,  lcom.`anon_name` AS l_anon_name
				FROM `".BIT_DB_PREFIX."liberty_comments` lcom
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcom.`content_id` = lc.`content_id`)
					LEFT JOIN `${BIT_DB_PREFIX}boards_post` post ON (post.`comment_id` = lcom.`comment_id`)
				WHERE (".$substrSql.") LIKE '".sprintf("%09d.",$data['th_thread_id'])."%' $whereSql
				ORDER BY lc.`last_modified` DESC
	    ";
		$result = $this->mDb->getRow( $query);
		if (empty($result['l_anon_name'])) $result['l_anon_name'] = "Anonymous";
		return $result;
	}

	/**
	* Generates the URL to the bitboard page
	* @param pExistsHash the hash that was returned by LibertyAttachable::pageExists
	* @return the link to display the page.
	*/
	function getDisplayUrl() {
		$ret = NULL;
		if( @$this->verifyId( $this->mRootId ) ) {
			$ret=BITBOARDS_PKG_URL."index.php?t=".$this->mRootId;
		}
		return $ret;
	}

	function isLocked($thread_id=false) {
		global $gBitSystem;
		if (!$thread_id) {
			$thread_id = $this->mRootId;
		} else {
			$thread_id=intval($thread_id);
		}
		$ret = $gBitSystem->mDb->getOne("SELECT `is_locked` FROM `".BIT_DB_PREFIX."boards_topics` WHERE `parent_id` = $thread_id");
		return !empty($ret);
	}

	function isLockedMsg($parent_id) {
		$parentComment = new LibertyComment(NULL,$parent_id);
		$topic_id = $parentComment->mInfo['thread_forward_sequence'];
		if (!empty($topic_id)) {
			return BitBoardTopic::isLocked($topic_id);
		}
		return false;
	}

	function isNotificationOn($thread_id=false) {
		global $gBitSystem, $gBitUser;
		if ($gBitSystem->isPackageActive('bitboards') && $gBitSystem->isFeatureActive('bitboards_thread_track')) {
			if (!$thread_id) {
				$thread_id = $this->mRootId;
			}
			if (is_numeric($thread_id)) {
				$topic_id = sprintf("%09d.",$thread_id);
			}
			return $gBitSystem->mDb->getOne("SELECT SUM(`notify`) FROM `".BIT_DB_PREFIX."boards_tracking` WHERE topic_id='$topic_id'");
		}
		return false;
	}

	function getNotificationData($thread_id) {
		global $gBitSystem, $gBitUser;
		if ($gBitSystem->isPackageActive('bitboards') && $gBitSystem->isFeatureActive('bitboards_thread_track')) {
			if (!$thread_id) {
				$thread_id = $this->mRootId;
			}
			if (is_numeric($thread_id)) {
				$topic_id = sprintf("%09d.",$thread_id);
			}
			$query = "SELECT
			uu.user_id,
			uu.email,
			uu.login,
			uu.real_name,
			trk.`track_date`,
			trk.`notify` AS track_notify,
			trk.`notify_date` AS track_notify_date
			FROM `".BIT_DB_PREFIX."boards_tracking` trk
			LEFT JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id` = trk.`user_id` )
			WHERE topic_id='$topic_id'";

			$result = $gBitSystem->mDb->query($query);
			$ret = array();
			$ret['users']=array();
			while( $res = $result->fetchRow() ) {
				$res['user'] =( isset( $res['real_name'] )? $res['real_name'] : $res['login'] );
				$ret['users'][$res['login']] = $res;
			}
			$ret['topic'] = new BitBoardTopic(intval($topic_id));
			$ret['topic']->load();
			return $ret;
		}
		return array();
	}

	function sendNotification($user) {
		global $gBitSystem;
		$mail_subject= "Topic Reply Notification - ".$this->mInfo['title'];
		$host = 'http://'.$_SERVER['HTTP_HOST'];
		//TODO: use a template for this
		$mail_message = "Hello ".$user['user'].",

You are receiving this email because you are watching the topic, \"".$this->mInfo['title']."\" at ".$gBitSystem->getConfig('site_title',"[Bitweaver Site]").".
This topic has received a reply since your last visit.
You can use the following link to view the replies made, no more notifications will be sent until you visit the topic.

".$host.$this->getDisplayUrl()."

If you no longer wish to watch this topic you can either click the \"Stop watching this topic link\" found at the topic of the topic above, or by clicking the following link after logging on:

".$host.$this->getDisplayUrl()."&notify=1";


		@mail($user['email'], $mail_subject , $mail_message, "From: ".$gBitSystem->getConfig( 'site_sender_email' )."\r\nContent-type: text/plain;charset=utf-8\r\n");

		$data = array(
		'notify_date'=>time(),
		);

		$key = array(
		'user_id' =>$user['user_id'],
		'topic_id' =>sprintf("%09d.",$this->mRootId),
		);
		$this->mDb->associateUpdate(BIT_DB_PREFIX."boards_tracking",$data,$key);
	}

	function getFlipFlop($arr=false) {
		if(! $arr) {
			$arr = $this->mInfo;
		}
		global $gBitSmarty, $gBitSystem, $gBitUser;

		if ($gBitSystem->isFeatureActive('bitboards_thread_track') && $gBitUser->isRegistered()) {
			$flip['new']['state']=($arr['track']['on']&&$arr['track']['mod'])*1;
			$flip['new']['req']=4;
			$flip['new']['id']=$arr['th_thread_id'];
			$flip['new']['idname']='t';
			$flip['new']['up']='folder-new';
			$flip['new']['upname']='New Posts';
			$flip['new']['down']='folder';
			$flip['new']['downname']='No new posts';
			$flip['new']['perm']='p_bitboards_read';
		}
		if ($gBitSystem->isFeatureActive('bitboards_thread_notification') && $gBitUser->isRegistered()) {
			$flip['notify']['state']=($arr['notify']['on'])*1;
			$flip['notify']['req']=5;
			$flip['notify']['id']=$arr['th_thread_id'];
			$flip['notify']['idname']='t';
			$flip['notify']['up']='mail-send-receive';
			$flip['notify']['upname']='Reply Notification';
			$flip['notify']['down']='internet-mail';
			$flip['notify']['downname']='Reply Notification Disabled';
			$flip['notify']['perm']='p_bitboards_read';
		}

		$flip['is_locked']['state']=$arr['th_is_locked'];
		$flip['is_locked']['req']=2;
		$flip['is_locked']['id']=$arr['th_thread_id'];
		$flip['is_locked']['idname']='t';
		$flip['is_locked']['up']='emblem-readonly';
		$flip['is_locked']['upname']='Thread Locked';
		$flip['is_locked']['down']='internet-group-chat';
		$flip['is_locked']['downname']='Thread Unis_locked';
		$flip['is_locked']['perm']='p_bitboards_edit';

		$flip['is_sticky']['state']=$arr['th_is_sticky'];
		$flip['is_sticky']['req']=3;
		$flip['is_sticky']['id']=$arr['th_thread_id'];
		$flip['is_sticky']['idname']='t';
		$flip['is_sticky']['up']='emblem-important';
		$flip['is_sticky']['upname']='Sticky Thread';
		$flip['is_sticky']['down']='media-playback-stop';
		$flip['is_sticky']['downname']='Non Sticky Thread';
		$flip['is_sticky']['perm']='p_bitboards_edit';

		return $flip;
	}

	function readTopic() {
		global $gBitUser, $gBitSystem;
		if ($gBitSystem->isFeatureActive('bitboards_thread_track') && $gBitUser->isRegistered()) {
			$topic_id = sprintf("%09d.",$this->mRootId);
			$BIT_DB_PREFIX = BIT_DB_PREFIX;
			$c = $this->mDb->getOne("SELECT COUNT(*) FROM `".BIT_DB_PREFIX."boards_tracking` WHERE user_id=? AND topic_id='$topic_id'",array($gBitUser->mUserId));

			$data = array(
			'user_id' =>$gBitUser->mUserId,
			'topic_id' =>$topic_id,
			'track_date'=>time(),
			);

			if ($c == 0) {
				$this->mDb->associateInsert(BIT_DB_PREFIX."boards_tracking",$data);
			} else {
				$key = array(
				'user_id' =>$gBitUser->mUserId,
				'topic_id' =>$topic_id,
				);
				$this->mDb->associateUpdate(BIT_DB_PREFIX."boards_tracking",$data,$key);
			}
			$this->mInfo['track']['mod']=false;
		}
	}

	function readTopicSet($pState) {
		global $gBitUser, $gBitSystem;
		if ($gBitSystem->isFeatureActive('bitboards_thread_track') && $gBitUser->isRegistered()) {
			$topic_id = sprintf("%09d.",$this->mRootId);
			$ret = FALSE;
			if ($pState==null || !is_numeric($pState) || $pState > 1 || $pState<0) {
				$this->mErrors[]=("Invalid current state");
			} else {
				$pState = (($pState+1)%2);
				if ($pState == 0) {
					$this->readTopic();
				} else {
					$this->mDb->query("DELETE FROM `".BIT_DB_PREFIX."boards_tracking` WHERE user_id=$gBitUser->mUserId AND topic_id='$topic_id'");
				}
				$ret = true;
			}
			return $ret;
		}
	}

	function notify($pState) {
		global $gBitUser, $gBitSystem;
		if ($gBitSystem->isFeatureActive('bitboards_thread_track') && $gBitUser->isRegistered()) {
			$topic_id = sprintf("%09d.",$this->mRootId);
			$ret = FALSE;
			if ($pState==null || !is_numeric($pState) || $pState > 1 || $pState<0) {
				$this->mErrors[]=("Invalid current state");
			} else {
				$pState = (($pState+1)%2);
				$query_sel = "SELECT * FROM `".BIT_DB_PREFIX."boards_tracking` WHERE user_id=$gBitUser->mUserId AND topic_id='$topic_id'";
				$data = array(
				'user_id' =>$gBitUser->mUserId,
				'topic_id' =>$topic_id,
				'notify'=>$pState,
				);
				$c = $this->mDb->getOne( $query_sel );
				if ($c == 0) {
					$this->mDb->associateInsert(BIT_DB_PREFIX."boards_tracking",$data);
				} else {
					$key = array(
					'user_id' =>$gBitUser->mUserId,
					'topic_id' =>$topic_id,
					);
					$this->mDb->associateUpdate(BIT_DB_PREFIX."boards_tracking",$data,$key);
				}
				$ret = true;
			}
			return $ret;
		}
	}

	function loadTrack(&$selectSql,&$joinSql) {
		global $gBitUser, $gBitSystem;
		if($gBitUser->isRegistered() && ($gBitSystem->isFeatureActive('bitboards_thread_track') || $gBitSystem->isFeatureActive('bitboards_thread_notify'))) {
			$selectSql .= ", trk.`track_date`,  trk.`notify` AS track_notify, trk.`notify_date` AS track_notify_date ";
			$joinSql .= " LEFT JOIN `".BIT_DB_PREFIX."boards_tracking` trk ON (trk.`topic_id`=lcom.`thread_forward_sequence` AND ( trk.`user_id` = ".$gBitUser->mUserId." OR trk.`user_id` IS NULL ) ) ";
		}
	}

	function track(&$res) {
		global $gBitUser, $gBitSystem;
		if($gBitUser->isRegistered() && $gBitSystem->isFeatureActive('bitboards_thread_track') && $res['th_is_moved']<=0) {
			$res['track']['on'] = true;
			$res['track']['date'] = $res['track_date'];
			if (empty($res['llc_last_modified'])) {
				$res['llc_last_modified']=0;
			}
			if ($res['llc_last_modified']>$res['track_date']) {
				$res['track']['mod'] = true;
			} else {
				$res['track']['mod'] = false;
			}
		}  else {
			$res['track']['on'] = false;
		}
		unset($res['track_date']);
		if($gBitUser->isRegistered() && $gBitSystem->isFeatureActive('bitboards_thread_notification') && $res['th_is_moved']<=0) {
			$res['notify']['on'] = (!empty($res['track_notify']));
			if ($res['notify']['on']) {
				$res['notify']['date']=$res['track_notify_date'];
			}
		} else {
			$res['notify']['on'] = false;
		}
		unset($res['track_notify_date']);
		unset($res['track_notify']);
	}
}
?>
