<?php
/**
* $Header: /cvsroot/bitweaver/_bit_boards/BitBoardTopic.php,v 1.3 2006/07/06 19:44:26 hash9 Exp $
* $Id: BitBoardTopic.php,v 1.3 2006/07/06 19:44:26 hash9 Exp $
*/

/**
* Messageboards class to illustrate best practices when creating a new bitweaver package that
* builds on core bitweaver functionality, such as the Liberty CMS engine
*
* @date created 2004/8/15
* @author spider <spider@steelsun.com>
* @version $Revision: 1.3 $ $Date: 2006/07/06 19:44:26 $ $Author: hash9 $
* @class BitBoardTopic
*/

require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );

/**
* This is used to uniquely identify the object
*/
define( 'BitBoardTopic_CONTENT_TYPE_GUID', 'BitForumTopic' );

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
		global $gBitUser;
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

			if (!($gBitUser->hasPermission('p_bitboards_edit'))) {
				//$whereSql .= " AND (th.`deleted` = 0)";
			}

			$BIT_DB_PREFIX = BIT_DB_PREFIX;
			$query ="
SELECT
	lc.`user_id` AS flc_user_id,
	lc.`created` AS flc_created,
	lc.`last_modified` AS flc_last_modified,
	lc.`title` AS flc_title,
	lc.`title` AS title,

	COALESCE(post.`approved`,0) AS first_approved,
	COALESCE(post.`deleted`,0) AS first_deleted,

	th.`parent_id` AS th_first_id,
	COALESCE(th.`locked`,0) AS th_locked,
	COALESCE(th.`moved`,0) AS th_moved,
	COALESCE(th.`sticky`,0) AS th_sticky,
	COALESCE(th.`deleted`,0) AS th_deleted,


	lcom.`comment_id` AS th_thread_id,
	lcom.`root_id` AS th_root_id,

	lcom.`root_id` AS content_id,
	lc.`content_type_guid` AS content_type_guid

	$selectSql
		FROM `${BIT_DB_PREFIX}liberty_comments` AS lcom
		INNER JOIN `${BIT_DB_PREFIX}liberty_content` AS lc ON( lc.`content_id` = lcom.`content_id` )
		LEFT JOIN `${BIT_DB_PREFIX}forum_thread` th ON (th.`parent_id`=lcom.`comment_id`)
		LEFT JOIN `${BIT_DB_PREFIX}forum_post` AS post ON(post.`comment_id`=lcom.`comment_id`)
		$joinSql
WHERE
	lcom.`root_id`=lcom.`parent_id` AND	$lookupColumn=?
	$whereSql";
			$result = $this->mDb->query( $query, $bindVars );
			if( $result && $result->numRows() ) {
				$this->mInfo = $result->fields;
				$this->mRootId = $result->fields['th_thread_id'];

				$this->mInfo['display_url'] = $this->getDisplayUrl();

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
		$query = "DELETE FROM `".BIT_DB_PREFIX."forum_topic` WHERE `thread_id` = ?";
		$result = $this->mDb->query( $query, array( $this->mRootId ) );
		$this->mDb->CompleteTrans();
		return $ret;
	}

	/**
	* This function locks a thread
	**/
	function lock($state) {
		global $gBitSystem;
		$ret = FALSE;
		if ($state==null || !is_numeric($state) || $state > 1 || $state<0) {
			$this->mErrors[]=("Invalid current state");
		} else {
			$gBitSystem->verifyPermission('p_bitboards_edit');
			$state = (($state+1)%2);
			$query_sel = "SELECT * FROM `".BIT_DB_PREFIX."forum_thread` WHERE `parent_id` = ?";
			$query_ins = "INSERT INTO `".BIT_DB_PREFIX."forum_thread` (`parent_id`,`locked`) VALUES ( ?, $state)";
			$query_up = "UPDATE `".BIT_DB_PREFIX."forum_thread` SET `locked` = $state WHERE `parent_id` = ?";
			$result = $this->mDb->query( $query_sel, array( $this->mRootId ) );
			if($result->RowCount()==0) {
				$result = $this->mDb->query( $query_ins, array( $this->mRootId ) );
			} else {
				$result = $this->mDb->query( $query_up, array( $this->mRootId ) );
			}
			$ret = true;
		}
		return $ret;
	}

	/**
	* This function stickies a thread
	**/
	function sticky($state) {
		global $gBitSystem;
		$ret = FALSE;
		if ($state==null || !is_numeric($state) || $state > 1 || $state<0) {
			$this->mErrors[]=("Invalid current state");
		} else {
			$gBitSystem->verifyPermission('p_bitboards_edit');
			$state = (($state+1)%2);
			$query_sel = "SELECT * FROM `".BIT_DB_PREFIX."forum_thread` WHERE `parent_id` = ?";
			$query_ins = "INSERT INTO `".BIT_DB_PREFIX."forum_thread` (`parent_id`,`sticky`) VALUES ( ?, $state)";
			$query_up = "UPDATE `".BIT_DB_PREFIX."forum_thread` SET `sticky` = $state WHERE `parent_id` = ?";
			$result = $this->mDb->query( $query_sel, array( $this->mRootId ) );
			if($result->RowCount()==0) {
				$result = $this->mDb->query( $query_ins, array( $this->mRootId ) );
			} else {
				$result = $this->mDb->query( $query_up, array( $this->mRootId ) );
			}
			$ret = TRUE;
		}
		return $ret;
	}

	/**
	* This function moves a thread to a new messageboard
	**/
	function moveTo($board_id) {
		$ret = FALSE;
		$this->mDb->StartTrans();
		$lcom = new LibertyComment();
		$lcom_hash['data']="The comments from: {$this->mInfo['title']} ({$this->mRootId}) have been moved to $board_id";
		$lcom_hash['title']=$this->mInfo['title'];
		$lcom_hash['parent_id']=$this->mInfo['th_root_id'];
		$lcom_hash['root_id']=$this->mInfo['th_root_id'];
		$lcom_hash['created']=$this->mInfo['flc_created'];
		$lcom_hash['last_modified']=$this->mInfo['flc_last_modified'];
		$lcom->storeComment($lcom_hash);
		$lcom->mCommentId;
		$data = array();
		$data['parent_id']=$lcom->mCommentId;
		$data['moved']=$this->mRootId;
		$this->mDb->associateInsert( BIT_DB_PREFIX."forum_thread", $data );
		$query = "UPDATE `".BIT_DB_PREFIX."liberty_comments`
			SET
				`root_id` = $board_id
			WHERE
				`thread_forward_sequence` LIKE '".sprintf("%09d.", $this->mRootId)."%'";

		$query = "UPDATE `".BIT_DB_PREFIX."liberty_comments`
			SET
				`root_id` = $board_id,
				`parent_id` = $board_id
			WHERE
				`thread_forward_sequence` LIKE '".sprintf("%09d.", $this->mRootId)."%'
				AND `root_id`=`parent_id`
				";
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
			$whereSql .= " AND UPPER( flc.`title` )like ? ";
			$bindVars[] = '%' . strtoupper( $find ). '%';
		}

		if(!empty($pParamHash['c'])) {
			$whereSql .= " AND lcom.`root_id` = ". $pParamHash['c'] ;
		}


		if (!($gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit'))) {
			/*$whereSql .= " AND ((post.`approved` = TRUE) OR (lc.`user_id` >= 0))";
			$selectSql = " 0 AS unreg";*/
		} else {
			/*$selectSql = "( SELECT COUNT(*)
			FROM `${BIT_DB_PREFIX}forum_post` s
			INNER JOIN `${BIT_DB_PREFIX}liberty_comments` AS lcom ON( lcom.`comment_id` = s.`comment_id` )
			INNER JOIN `${BIT_DB_PREFIX}liberty_content` lc ON( lc.`content_id` = lcom.`content_id` )
			WHERE s.`deleted`=FALSE AND ((s.`approved` = FALSE) AND (lc.`user_id` < 0)) AND s.`thread_id` = th.`thread_id`
			) AS unreg";*/
		}
		if (!($gBitUser->hasPermission('p_bitboards_edit'))) {
			//$whereSql .= " AND (th.`deleted` = FALSE)";
		}

		$sort_sql = "flc.".$this->mDb->convert_sortmode( $sort_mode );
		//flc.*, first.*, th.*, last.*, llc.*
		/*
		(
		SELECT COUNT(*)
		FROM `${BIT_DB_PREFIX}mb_post` s
		INNER JOIN `${BIT_DB_PREFIX}liberty_content` lc ON( lc.`content_id` = s.`content_id` )
		WHERE s.`deleted` = FALSE AND ((s.`approved` = TRUE) OR (lc.`user_id` >= 0)) AND s.`thread_id` = th.`thread_id`
		) AS post_count,
		post.`unreg_uname` AS first_unreg_uname,
		unreg DESC,
		*/
		$query = <<<SQL
SELECT
	lc.`user_id` AS flc_user_id,
	lc.`created` AS flc_created,
	lc.`last_modified` AS flc_last_modified,
	lc.`title` AS flc_title,
	lc.`title` AS title,

	COALESCE(post.`approved`,0) AS first_approved,
	COALESCE(post.`deleted`,0) AS first_deleted,

	th.`parent_id` AS th_first_id,
	COALESCE(th.`locked`,0) AS th_locked,
	COALESCE(th.`moved`,0) AS th_moved,
	COALESCE(th.`sticky`,0) AS th_sticky,
	COALESCE(th.`deleted`,0) AS th_deleted,

	lcom.`comment_id` AS th_thread_id,
	lcom.`root_id` AS th_root_id,

	lcom.`root_id` AS content_id,
	lc.`content_type_guid` AS content_type_guid

	$selectSql
		FROM `${BIT_DB_PREFIX}liberty_comments` AS lcom
		INNER JOIN `${BIT_DB_PREFIX}liberty_content` AS lc ON( lc.`content_id` = lcom.`content_id` )
		LEFT JOIN `${BIT_DB_PREFIX}forum_thread` th ON (th.`parent_id`=lcom.`comment_id`)
		LEFT JOIN `${BIT_DB_PREFIX}forum_post` AS post ON (post.`comment_id` = lcom.`comment_id`)
		$joinSql
WHERE
	lcom.`root_id`=lcom.`parent_id`
	$whereSql
ORDER BY
	th_sticky DESC,
	th_moved ASC,
	th_deleted ASC,
	lc.created DESC
SQL;
		$query_cant  = <<<SQL
SELECT count(*)
FROM `${BIT_DB_PREFIX}liberty_comments` AS lcom
INNER JOIN `${BIT_DB_PREFIX}liberty_content` AS lc ON( lc.`content_id` = lcom.`content_id` )
LEFT JOIN `${BIT_DB_PREFIX}forum_thread` th ON (th.`parent_id`=lcom.`comment_id`)
LEFT JOIN `${BIT_DB_PREFIX}forum_post` AS post ON (post.`comment_id` = lcom.`comment_id`)
$joinSql
WHERE
	lcom.`root_id`=lcom.`parent_id`
	$whereSql
SQL;
		$result = $this->mDb->query( $query, $bindVars, $max_records, $offset );
		$ret = array();
		while( $res = $result->fetchRow() ) {
			if ($res['th_moved']>0) {
				$res['url']=BITBOARDS_PKG_URL."index.php?t=".$res['th_moved'];
			} else {
				$res['url']=BITBOARDS_PKG_URL."index.php?t=".$res['th_thread_id'];
			}
			$res['flip']=BitBoardTopic::getFlipFlop($res);
			$ret[] = $res;
		}
		$pParamHash["cant"] = $this->mDb->getOne( $query_cant, null );
		// add all pagination info to pParamHash
		LibertyAttachable::postGetList( $pParamHash );
		return $ret;
}

/**
	* Generates the URL to the bitboard page
	* @param pExistsHash the hash that was returned by LibertyAttachable::pageExists
	* @return the link to display the page.
	*/
function getDisplayUrl() {
	$ret = NULL;
	if( @$this->verifyId( $this->mRootId ) ) {
		$res['url']=BITBOARDS_PKG_URL."index.php?c=".$this->mContentId;
	}
	return $ret;
}

function isLocked($thread_id=false) {
	global $gBitSystem;
	if (!$thread_id) {
		$thread_id = $this->mRootId;
	}
	return $gBitSystem->mDb->getOne("SELECT `locked` FROM `".BIT_DB_PREFIX."forum_thread` WHERE `parent_id` = $thread_id");
}

function getFlipFlop($arr=false) {
	if(! $arr) {
		$arr = $this->mInfo;
	}
	global $gBitSmarty;
	$flip['locked']['state']=$arr['th_locked'];
	$flip['locked']['req']=2;
	$flip['locked']['id']=$arr['th_thread_id'];
	$flip['locked']['idname']='t';
	$flip['locked']['up']='locked';
	$flip['locked']['upname']='Thread Locked';
	$flip['locked']['down']='unlocked';
	$flip['locked']['downname']='Thread Unlocked';

	$flip['sticky']['state']=$arr['th_sticky'];
	$flip['sticky']['req']=3;
	$flip['sticky']['id']=$arr['th_thread_id'];
	$flip['sticky']['idname']='t';
	$flip['sticky']['up']='idea';
	$flip['sticky']['upname']='Sticky Thread';
	$flip['sticky']['down']='agt_uninstall-product';
	$flip['sticky']['downname']='Non Sticky Thread';
	return $flip;
}
}
?>
