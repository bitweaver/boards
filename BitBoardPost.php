<?php
/**
* $Header: /cvsroot/bitweaver/_bit_boards/BitBoardPost.php,v 1.2 2006/07/06 14:31:21 hash9 Exp $
* $Id: BitBoardPost.php,v 1.2 2006/07/06 14:31:21 hash9 Exp $
*/

/**
* Messageboards class to illustrate best practices when creating a new bitweaver package that
* builds on core bitweaver functionality, such as the Liberty CMS engine
*
* @date created 2004/8/15
* @author spider <spider@steelsun.com>
* @version $Revision: 1.2 $ $Date: 2006/07/06 14:31:21 $ $Author: hash9 $
* @class BitMBPost
*/

require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardTopic.php' );

class BitBoardPost extends LibertyComment {
	/**
	* During initialisation, be sure to call our base constructors
	**/
	function BitBoardPost($pCommentId = NULL, $pContentId = NULL, $pInfo = NULL) {
		LibertyComment::LibertyComment($pCommentId,$pContentId,$pInfo);
	}

	/**
	* This function removes a bitboard entry
	**/
	function expungeMeta() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$query = "UPDATE `".BIT_DB_PREFIX."forum_post` SET `deleted` = 1 WHERE `comment_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mCommentId ) );
			$ret = TRUE;
		}
		return $ret;
	}

	function getComments_flat( $pContentId = NULL, $pMaxComments = NULL, $pOffset = NULL, $pSortOrder = NULL, $root_comment_id=1 ) {
		static $curLevel = 0;

		$ret = array();
		$contentId = $this->mCommentId;

		$mid = "";

		$sort_order = "ASC";
		$mid = 'last_modified ASC';
		if (!empty($pSortOrder)) {
			if ($pSortOrder == 'commentDate_desc') {
				$mid = 'last_modified DESC';
			} else if ($pSortOrder == 'commentDate_asc') {
				$mid = 'last_modified ASC';
			} elseif ($pSortOrder == 'thread_asc') {
				$mid = 'thread_forward_sequence  ASC';
				// thread newest first is harder...
			} elseif ($pSortOrder == 'thread_desc') {
				$mid = 'thread_reverse_sequence  ASC';
			} else {
				$mid = $this->mDb->convert_sortmode( $pSortOrder );
			}
		}
		$mid = 'thread_forward_sequence  ASC';
		$mid = 'order by ' . $mid;

		$bindVars = array();
		if (is_array( $contentId ) ) {
			$mid2 = 'in ('.implode(',', array_fill(0, count( $pContentId ), '?')).')';
			$bindVars = $contentId;
			$select1 = ', lcp.content_type_guid as parent_content_type_guid, lcp.title as parent_title ';
			$join1 = " LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lcp ON (lcp.content_id = lcom.parent_id) ";
		} elseif ($contentId) {
			$mid2 = "`thread_forward_sequence` LIKE '".sprintf("%09d.",$contentId)."%'";
			$select1 = '';
			$join1 = '';
		}

		$joinSql = $selectSql = $whereSql = '';
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, $this );

		if ($pContentId) {
			$sql = "SELECT lcom.comment_id, lcom.parent_id, lcom.root_id, lcom.thread_forward_sequence,
				lcom.thread_reverse_sequence, lc.*, uu.`email`, uu.`real_name`, uu.`login`, post.*, uu.registration_date AS registration_date $selectSql $select1
					FROM `".BIT_DB_PREFIX."liberty_comments` lcom
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcom.`content_id` = lc.`content_id`)
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id` = uu.`user_id`) $joinSql $join1
						LEFT JOIN `".BIT_DB_PREFIX."forum_post` AS post ON (post.`comment_id` = lcom.`comment_id`)
				    WHERE $mid2 $whereSql $mid";
			$flat_comments = array();

			if( $result = $this->mDb->query( $sql, $bindVars, $pMaxComments, $pOffset ) ) {
				while( $row = $result->FetchRow() ) {
					$row['parsed_data'] = $this->parseData( $row );
					$row['level'] = substr_count ( $row['thread_forward_sequence'], '.' ) - 1;
					$flat_comments[] = $row;
					//va($row);
				}
			}

			# now select comments wanted
			$ret = $flat_comments;

		}
		return $ret;
	}
	/**
	* Generates the URL to the bitboard page
	* @param pExistsHash the hash that was returned by LibertyAttachable::pageExists
	* @return the link to display the page.
	*/
	function getDisplayUrl() {
		$ret = NULL;
		if( @$this->verifyId( $this->mMBPostId ) ) {
			$ret = BITBOARDS_PKG_PATH_PKG_URL."list_posts.php?thread_id=".$this->mInfo['thread_id']."#".$this->mMBPostId;
		}
		return $ret;
	}

	function mod_approve() {
		$query = "UPDATE `".BIT_DB_PREFIX."forum_post` SET `approved` = 1 WHERE `post_id` = ?";
		$result = $this->mDb->query( $query, array( $this->mMBPostId ) );
	}

	function mod_reject() {
		/*$query = "DELETE FROM `".BIT_DB_PREFIX."forum_post` WHERE `post_id` = ?";
		$result = $this->mDb->query( $query, array( $this->mMBPostId ) );*/
		$this->expunge();
	}

	function mod_warn($message) {
		global $gBitSystem;
		$gBitSystem->fatalError("No Warning Message Given. <br>\nA post cannot be warned without a message");
		$data['warned']=1;
		$data['warnmsg']=$message;
	}
}
?>
