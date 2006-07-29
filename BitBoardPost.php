<?php
/**
* $Header: /cvsroot/bitweaver/_bit_boards/BitBoardPost.php,v 1.6 2006/07/29 15:10:00 hash9 Exp $
* $Id: BitBoardPost.php,v 1.6 2006/07/29 15:10:00 hash9 Exp $
*/

/**
* Messageboards class to illustrate best practices when creating a new bitweaver package that
* builds on core bitweaver functionality, such as the Liberty CMS engine
*
* @date created 2004/8/15
* @author spider <spider@steelsun.com>
* @version $Revision: 1.6 $ $Date: 2006/07/29 15:10:00 $ $Author: hash9 $
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

	function loadMetaData() {
		if ($this->isValid()) {
			if (!isset($this->mInfo['accepted'])) {
				$key = array('comment_id' => $this->mCommentId);
				$query_sel = "SELECT
				post.approved,
				post.warned,
				post.warned_message
				FROM `".BIT_DB_PREFIX."forum_post` post WHERE comment_id=?";
				$data = $this->mDb->getRow( $query_sel , array_values($key));
				if ($data) {
					if (!empty($data['warned_message'])) {
						$data['warned_message'] = str_replace("\n","<br />\n",$data['warned_message']);
					}
					$this->mInfo=array_merge($this->mInfo,$data);
				}
			}
		}
	}

	/**
	* This function removes a bitboard entry
	**/
	function expungeMetaData($comment_id=false) {
		if (isset($this)) {
			$comment_id = $this->mCommentId;
		}
		$ret = FALSE;
		if( @BitBase::verifyId($comment_id) ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."forum_post` WHERE `comment_id` = ?";
			$result = $this->mDb->query( $query, array( $comment_id ) );
			$ret = TRUE;
		}
		return $ret;
	}

	function getComments( $pContentId = NULL, $pMaxComments = NULL, $pOffset = NULL, $pSortOrder = NULL, $pDisplayMode = NULL ) {
		global $gBitUser, $gBitSystem;

		$joinSql = $selectSql = $whereSql = '';

		$ret = array();
		$contentId = $this->mCommentId;

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

		if ($gBitSystem->isFeatureActive('bitboards_post_anon_moderation') && !($gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit'))) {
			$whereSql .= " AND ((post.`approved` = 1) OR (lc.`user_id` >= 0))";
		}

		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, $this );

		if ($pContentId) {
			$sql = "SELECT lcom.`comment_id`, lcom.`parent_id`, lcom.`root_id`,
			lcom.`thread_forward_sequence`, lcom.`thread_reverse_sequence`, lcom.`anon_name`, lc.*, uu.`email`, uu.`real_name`, uu.`login`,
				post.approved,
				post.warned,
				post.warned_message,
				uu.registration_date AS registration_date,
				tf_ava.`storage_path` AS `avatar_storage_path`
				$selectSql $select1
					FROM `".BIT_DB_PREFIX."liberty_comments` lcom
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcom.`content_id` = lc.`content_id`)
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id` = uu.`user_id`)
						 $joinSql $join1
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ta_ava ON ( uu.`avatar_attachment_id`=ta_ava.`attachment_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` tf_ava ON ( tf_ava.`file_id`=ta_ava.`foreign_id` )
						LEFT JOIN `".BIT_DB_PREFIX."forum_post` AS post ON (post.`comment_id` = lcom.`comment_id`)
				    WHERE $mid2 $whereSql $mid";

			$flat_comments = array();

			if( $result = $this->mDb->query( $sql, $bindVars, $pMaxComments, $pOffset ) ) {
				while( $row = $result->FetchRow() ) {
					if (empty($row['anon_name'])) $row['anon_name'] = "Anonymous";
					$row['user_avatar_url'] = (!empty($row['avatar_storage_path']) ? BIT_ROOT_URL . dirname( $row['avatar_storage_path'] ).'/avatar.jpg' : NULL);
					unset($row['avatar_storage_path']);
					if (!empty($row['warned_message'])) {
						$row['warned_message'] = str_replace("\n","<br />\n",$row['warned_message']);
					}
					$row['data'] = trim($row['data']);
					$row['user_url']=BitUser::getDisplayUrl($row['login'],$row);
					$row['parsed_data'] = $this->parseData( $row );
					$row['level'] = substr_count ( $row['thread_forward_sequence'], '.' ) - 1;
					$c = new LibertyComment();
					$c->mInfo=$row;
					$row['editable'] = $c->userCanEdit();
					$flat_comments[] = $row;
					//va($row);
				}
			}

			# now select comments wanted
			$ret = $flat_comments;

		}
		return $ret;
	}

	function getNumComments($pContentId = NULL) {
		$ret = 0;

		$contentId = $this->mCommentId;

		$bindVars = array();

		$joinSql = $selectSql = $whereSql = '';
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, $this );

		if ($pContentId) {
			$sql = "SELECT COUNT(*)
					FROM `".BIT_DB_PREFIX."liberty_comments` lcom
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcom.`content_id` = lc.`content_id`)
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id` = uu.`user_id`) $joinSql
						LEFT JOIN `".BIT_DB_PREFIX."forum_post` AS post ON (post.`comment_id` = lcom.`comment_id`)
					WHERE lcom.`thread_forward_sequence` LIKE '".sprintf("%09d.",$contentId)."%' $whereSql
			";
			$ret = $this->mDb->getOne( $sql, $bindVars );
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
		if( @$this->verifyId( $this->mCommentId ) ) {
			$ret = BITBOARDS_PKG_URL."index.php?t=".$this->getTopicId()."#".$this->mCommentId;
		}
		return $ret;
	}

	function getTopicId() {
		return intval(substr($this->mInfo['thread_forward_sequence'],0,9));
	}

	function mod_approve() {
		$data['approved'] = 1;
		$this->setMetaData($data);
	}

	function mod_reject() {
		$this->deleteComment();
	}

	function mod_warn($message) {
		global $gBitSystem, $gBitUser;

		if (empty($message)) {
			$gBitSystem->fatalError("No Warning Message Given. <br>\nA post cannot be warned without a message");
		}
		$data['warned']=1;
		$data['warned_message']=$message;
		$this->setMetaData($data);

		if ($gBitSystem->isPackageActive('messages')) {
			require_once(MESSAGES_PKG_PATH.'messages_lib.php');

			$u = new BitUser($this->mInfo['user_id']);
			$u->load();
			$userInfo = $u->mInfo;

			$pm = new Messages();
			$message = "Your post \"".$this->mInfo['title']."\" [http://".$_SERVER['HTTP_HOST'].$this->getDisplayUrl()."] has been warned with the following message:\n$message\n";
			$pm->post_message($userInfo['login'],$userInfo['real_name'],null,null,"Warned Post \"".$this->mInfo['title']."\"",$message,4);
		}
	}

	function setMetaData($data) {
		if ($this->isValid()) {
			$key = array('comment_id' => $this->mCommentId);
			$query_sel = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."forum_post` WHERE comment_id=?";
			$c = $this->mDb->getOne( $query_sel , array_values($key));
			if ($c == 0) {
				$data=array_merge($data,$key);
				$this->mDb->associateInsert(BIT_DB_PREFIX."forum_post",$data);
			} else {

				$this->mDb->associateUpdate(BIT_DB_PREFIX."forum_post",$data,$key);
			}
		}
	}
}
?>
