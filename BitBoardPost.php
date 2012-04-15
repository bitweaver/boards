<?php
/**
 * $Header$
 * $Id$
 *
 * Messageboards class to illustrate best practices when creating a new bitweaver package that
 * builds on core bitweaver functionality, such as the Liberty CMS engine
 *
 * @author spider <spider@steelsun.com>
 * @version $Revision$
 * @package boards
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );
require_once( BOARDS_PKG_PATH.'BitBoardTopic.php' );

/**
 * @package boards
 */
class BitBoardPost extends LibertyComment {
	/**
	* During initialisation, be sure to call our base constructors
	**/
	function BitBoardPost($pCommentId = NULL, $pContentId = NULL, $pInfo = NULL) {
		LibertyComment::LibertyComment($pCommentId,$pContentId,$pInfo);

		// Permission setup
		$this->mViewContentPerm  = 'p_boards_read';
		$this->mUpdateContentPerm  = 'p_boards_post_update';
		$this->mAdminContentPerm = 'p_boards_admin';
	}

	function verify( &$pParamHash ) {
		if( isset( $pParamHash['is_approved'] ) ) {
			if( !is_numeric( $pParamHash['is_approved'] ) || $pParamHash['is_approved'] > 1 || $pParamHash['is_approved'] < 0 ) {
				$this->mErrors[]=("Invalid post approved state");
			} else {
				$pParamHash['post_store']['is_approved'] = $pParamHash['is_approved'];
			}
		}
		if( isset( $pParamHash['is_warned'] ) ) {
			if( !is_numeric( $pParamHash['warned'] ) || $pParamHash['is_warned'] > 1 || $pParamHash['is_warned'] < 0 ) {
				$this->mErrors[]=("Invalid warned state");
			} else {
				$pParamHash['post_store']['is_warned'] = $pParamHash['is_warned'];
			}
		}
		if( !empty( $pParamHash['warned_message'] ) ) {
			$pParamHash['post_store']['warned_message'] = $pParamHash['warned_message'];
		}
		if( !empty( $pParamHash['warned_message'] ) ) {
			$pParamHash['post_store']['warned_message'] = $pParamHash['warned_message'];
		}
		if( !empty( $pParamHash['migrate_post_id'] ) ) {
			$pParamHash['post_store']['migrate_post_id'] = $pParamHash['migrate_post_id'];
		}

		return( count( $this->mErrors ) == 0 && !empty( $pParamHash['post_store'] ) );
	}

	/**
	* This function stores a post
	**/
	function store( &$pParamHash ) {
		global $gBitSystem;
		$ret = FALSE;
		if( $this->mCommentId && $this->verify( $pParamHash ) ) {
			//$gBitSystem->verifyPermission('p_boards_update');
			//$pParamHash = (($pParamHash + 1)%2);
			$query_sel = "SELECT * FROM `".BIT_DB_PREFIX."boards_posts` WHERE `comment_id` = ?";
			$isStored = $this->mDb->getOne( $query_sel, array( $this->mCommentId ) );
			if( $isStored ) {
				$result = $this->mDb->associateUpdate( 'boards_posts', $pParamHash['post_store'], array( 'comment_id' => $this->mCommentId ) );
			} else {
				$pParamHash['post_store']['comment_id'] = $this->mCommentId;
				$result = $this->mDb->associateInsert( 'boards_posts', $pParamHash['post_store'] );
			}
			$ret = TRUE;
		}
		return $ret;
	}	

	function loadMetaData() {
		if ($this->isValid()) {
			if (!isset($this->mInfo['accepted'])) {
				$key = array('comment_id' => $this->mCommentId);
				$query_sel = "SELECT
				post.is_approved,
				post.is_warned,
				post.warned_message
				FROM `".BIT_DB_PREFIX."boards_posts` post WHERE comment_id=?";
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
	function expunge() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			// parent actually has deletion of rows in boards for constraint reasons
			if( parent::expunge() ) {
				$this->mDb->CompleteTrans();
				$ret = TRUE;
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return $ret;
	}

	function getComments( $pContentId = NULL, $pMaxComments = NULL, $pOffset = NULL, $pSortOrder = NULL, $pDisplayMode = NULL ) {
		global $gBitUser, $gBitSystem;

		$joinSql = $selectSql = $whereSql = '';

		$ret = array();
		$contentId = $this->mCommentId;

		$mid = 'thread_forward_sequence  ASC';
		if (!empty($pSortOrder)) {
			if ($pSortOrder == 'commentDate_desc') {
				$mid = 'created DESC';
			} else if ($pSortOrder == 'commentDate_asc') {
				$mid = 'created ASC';
			} elseif ($pSortOrder == 'thread_asc') {
				$mid = 'thread_forward_sequence  ASC';
			// thread newest first is harder...
			} elseif ($pSortOrder == 'thread_desc') {
				$mid = 'thread_reverse_sequence  ASC';
			} else {
				$mid = $this->mDb->convertSortmode( $pSortOrder );
			}
		}
		$mid = 'order by ' . $mid;

		$bindVars = array();
		if (is_array( $contentId ) ) {
			$mid2 = 'in ('.implode(',', array_fill(0, count( $pContentId ), '?')).')';
			$bindVars = $contentId;
			$select1 = ', lcp.content_type_guid as parent_content_type_guid, lcp.title as parent_title ';
			$join1 = " LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lcp ON (lcp.content_id = lcom.parent_id) ";
		} elseif ($contentId) {
			$mid2 = "lcom.`thread_forward_sequence` LIKE '".sprintf("%09d.",$contentId)."%'";
			$select1 = '';
			$join1 = '';
		}

		if ($gBitSystem->isFeatureActive('boards_posts_anon_moderation') && !($gBitUser->hasPermission('p_boards_update') || $gBitUser->hasPermission('p_boards_post_update'))) {
			$whereSql .= " AND ((post.`is_approved` = 1) OR (lc.`user_id` >= 0))";
		}

        $pListHash = array( 'content_id' => $contentId, 'max_records' => $pMaxComments, 'offset'=>$pOffset, 'sort_mode'=> $pSortOrder, 'display_mode' => $pDisplayMode, 'has_comment_view_perm' => TRUE );
        $this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, $this, $pListHash );		

		if ($pContentId) {
			$sql = "SELECT lcom.`comment_id`, lcom.`parent_id`, lcom.`root_id`,
			lcom.`thread_forward_sequence`, lcom.`thread_reverse_sequence`, lcom.`anon_name`, lc.*, uu.`email`, uu.`real_name`, uu.`login`,
				post.is_approved,
				post.is_warned,
				post.warned_message,
				uu.registration_date AS registration_date,
				tf_ava.`file_name` AS `avatar_file_name`, tf_ava.`mime_type` AS `avatar_mime_type`, tf_ava.`user_id` AS `avatar_user_id`, ta_ava.`attachment_id` AS `avatar_attachment_id`
				$selectSql $select1
					FROM `".BIT_DB_PREFIX."liberty_comments` lcom
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcom.`content_id` = lc.`content_id`)
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id` = uu.`user_id`)
						 $joinSql $join1
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ta_ava ON ( uu.`avatar_attachment_id`=ta_ava.`attachment_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` tf_ava ON ( tf_ava.`file_id`=ta_ava.`foreign_id` )
						LEFT JOIN `".BIT_DB_PREFIX."boards_posts` post ON (post.`comment_id` = lcom.`comment_id`)
				    WHERE $mid2 $whereSql $mid";

			$flat_comments = array();

			if( $result = $this->mDb->query( $sql, $bindVars, $pMaxComments, $pOffset ) ) {
				while( $row = $result->FetchRow() ) {
					if (empty($row['anon_name'])) {
						$row['anon_name'] = "Anonymous";
					}
					if( $row['avatar_file_name'] ) {
						$row['user_avatar_url'] = liberty_fetch_thumbnail_url( array(
							'source_file' => liberty_mime_get_source_file( array( 'user_id'=>$row['avatar_user_id'], 'file_name'=>$row['avatar_file_name'], 'mime_type'=>$row['avatar_mime_type'], 'attachment_id'=>$row['avatar_attachment_id'] ) ),
							'size' => 'avatar'
						));
					} else {
						$row['user_avatar_url'] = FALSE;
					}
					if (!empty($row['warned_message'])) {
						$row['warned_message'] = str_replace("\n","<br />\n",$row['warned_message']);
					}
					$row['data'] = trim( $row['data'] );
					$row['user_url'] = BitUser::getDisplayUrl( $row['login'], $row );
					$row['parsed_data'] = $this->parseData( $row );
					$row['level'] = substr_count ( $row['thread_forward_sequence'], '.' ) - 1;
					$c = new LibertyComment();
					$c->mInfo=$row;
					$row['is_editable'] = $c->userCanEdit();
					
					if( $gBitSystem->isFeatureActive( 'comments_allow_attachments' ) ){
						// get attachments for each comment
						global $gLibertySystem;
						$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` la WHERE la.`content_id`=? ORDER BY la.`pos` ASC, la.`attachment_id` ASC";
						if( $result2 = $this->mDb->query( $query,array( (int)$row['content_id'] ))) {
							while( $row2 = $result2->fetchRow() ) {
								if( $func = $gLibertySystem->getPluginFunction( $row2['attachment_plugin_guid'], 'load_function', 'mime' )) {
									// we will pass the preferences by reference that the plugin can easily update them
									if( empty( $row['storage'][$row2['attachment_id']] )) {
										$row['storage'][$row2['attachment_id']] = array();
									}
									$row['storage'][$row2['attachment_id']] = $func( $row2, $row['storage'][$row2['attachment_id']] );
								} else {
									print "No load_function for ".$row2['attachment_plugin_guid'];
								}
							}
						}
						// end get attachements for each comment
					}
					
					$flat_comments[$row['content_id']] = $row;
					// vd($row);
				}
			}

			# now select comments wanted
			$ret = $flat_comments;

		}
		return $ret;
	}

	function getList( &$pListHash ) {
		global $gBitUser, $gBitSystem;

		$this->prepGetList( $pListHash );

		$joinSql = $selectSql = $whereSql = '';

		$ret = array();
		$contentId = $this->mCommentId;

//		$mid = 'ORDER BY `thread_forward_sequence` ASC';

		$bindVars = array();
		if( !empty( $pListHash['content_id'] ) ) {
			if (is_array( $contentId ) ) {
				$mid2 = 'in ('.implode(',', array_fill(0, count( $pListHash['content_id'] ), '?')).')';
				$bindVars = $contentId;
				$selectSql = ', lcp.content_type_guid as parent_content_type_guid, lcp.title as parent_title ';
				$joinSql .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lcp ON (lcp.content_id = lcom.parent_id) ";
			} elseif( is_numeric( $contentId ) ) {
				$whereSql .= " AND `thread_forward_sequence` LIKE '".sprintf("%09d.",$contentId)."%'";
			}
		}

		if ($gBitSystem->isFeatureActive('boards_posts_anon_moderation') && !($gBitUser->hasPermission('p_boards_update') || $gBitUser->hasPermission('p_boards_post_update'))) {
			$whereSql .= " AND ((post.`is_approved` = 1) OR (lc.`user_id` >= 0))";
		}

		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, $this );

		if( !empty( $pListHash['board_id'] ) ) {
			$joinSql .= "INNER JOIN `".BIT_DB_PREFIX."boards` b ON (b.`content_id` = bm.`board_content_id`)";
			$whereSql .= ' AND b.`board_id`=? ';
			array_push( $bindVars, (int)$pListHash['board_id'] );
		}

		if( BitBase::verifyId( $pListHash['user_id'] ) ) {
			$whereSql .= ' AND lc.`user_id`=? ';
			array_push( $bindVars, $pListHash['user_id'] );
		}

		if( !empty( $whereSql ) ) {
			$whereSql = preg_replace( '/^[\s]*AND\b/i', 'WHERE ', $whereSql );
		}

		$sql = "SELECT lcom.`comment_id`, lcom.`parent_id`, lcom.`root_id`, lcom.`thread_forward_sequence`, lcom.`thread_reverse_sequence`, lcom.`anon_name`, lc.*, uu.`email`, uu.`real_name`, uu.`login`, post.is_approved, post.is_warned, post.warned_message, uu.registration_date AS registration_date, 
					tf_ava.`file_name` AS `avatar_file_name`, tf_ava.`mime_type` AS `avatar_mime_type`, tf_ava.`user_id` AS `avatar_user_id`, ta_ava.`attachment_id` AS `avatar_attachment_id`
					$selectSql
				FROM `".BIT_DB_PREFIX."liberty_comments` lcom
					INNER JOIN `".BIT_DB_PREFIX."boards_map` bm ON (lcom.`root_id` = bm.`topic_content_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcom.`content_id` = lc.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id` = uu.`user_id`)
					 $joinSql
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ta_ava ON ( uu.`avatar_attachment_id`=ta_ava.`attachment_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` tf_ava ON ( tf_ava.`file_id`=ta_ava.`foreign_id` )
					LEFT JOIN `".BIT_DB_PREFIX."boards_posts` post ON (post.`comment_id` = lcom.`comment_id`)
				$whereSql ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] );

		$ret = array();

		if( $result = $this->mDb->query( $sql, $bindVars, $pListHash['max_records'], $pListHash['offset'] ) ) {
			while( $row = $result->FetchRow() ) {
				if (empty($row['anon_name'])) $row['anon_name'] = "Anonymous";
				if( !empty( $row['avatar_file_name'] )) {
					$row['user_avatar_url'] = liberty_fetch_thumbnail_url( array(
						'source_file' => liberty_mime_get_source_file( array( 'user_id'=>$row['avatar_user_id'], 'file_name'=>$row['avatar_file_name'], 'mime_type'=>$row['avatar_mime_type'], 'attachment_id'=>$row['avatar_attachment_id'] ) ),
						'size' => 'avatar'
					));
				} else {
					$row['user_avatar_url'] = FALSE;
				}
				unset($row['avatar_file_name']);
				if (!empty($row['warned_message'])) {
					$row['warned_message'] = str_replace("\n","<br />\n",$row['warned_message']);
				}
				$row['data'] = trim($row['data']);
				$row['user_url']=BitUser::getDisplayUrl($row['login'],$row);
				$row['parsed_data'] = $this->parseData( $row );
				$row['level'] = substr_count ( $row['thread_forward_sequence'], '.' ) - 1;
				$row['display_url'] = self::getDisplayUrl( $row['comment_id'], boards_get_topic_comment( $row['thread_forward_sequence'] ) );
				$c = new LibertyComment();
				$c->mInfo=$row;
				$row['is_editable'] = $c->userCanEdit();
				$ret[] = $row;
				//va($row);
			}
		}

		return $ret;
	}

	function getNumComments($pContentId = NULL) {
		$ret = 0;

		$contentId = $this->mCommentId;

		$bindVars = array();

		$joinSql = $selectSql = $whereSql = '';
		$paramHash = array( 'include_comments' => TRUE );
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, $this, $paramHash );

		if ($pContentId) {
			$sql = "SELECT COUNT(*)
					FROM `".BIT_DB_PREFIX."liberty_comments` lcom
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcom.`content_id` = lc.`content_id`)
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id` = uu.`user_id`) $joinSql
						LEFT JOIN `".BIT_DB_PREFIX."boards_posts` post ON (post.`comment_id` = lcom.`comment_id`)
					WHERE lcom.`thread_forward_sequence` LIKE '".sprintf("%09d.",$contentId)."%' $whereSql
			";
			$ret = $this->mDb->getOne( $sql, $bindVars );
		}
		return $ret;
	}

	/**
	* Generates the URL to the bitboard page
	* @return the link to display the page.
	*/
	public static function getDisplayUrl( $pCommentId=NULL, $pTopicId=NULL ) {
		global $gBitSystem;

		if( empty( $pCommentId ) || empty( $pTopicId ) ) {
			$pCommentId = $this->mCommentId;
			$pTopicId = $this->getTopicId();
		}
		$ret = NULL;
		if( @$this->verifyId( $pCommentId ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) || $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
				$rewrite_tag = $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ? 'view/':'';
				$ret = BOARDS_PKG_URL.$rewrite_tag."topic/".$pTopicId;
			} else {
				$ret = BOARDS_PKG_URL."index.php?t=".$pTopicId;
			}

			if( $pCommentId != $pTopicId ) {
				$ret .= '#comment_'.$pCommentId;
			}
		}
		return $ret;
	}

	function getTopicId() {
		return boards_get_topic_comment( $this->getField( 'thread_forward_sequence') );
	}

	function modApprove() {
		$data['is_approved'] = 1;
		$this->setMetaData($data);
	}

	function modReject() {
		$this->deleteComment();
	}

	function modWarn($message) {
		global $gBitSystem, $gBitUser;

		if (empty($message)) {
			$gBitSystem->fatalError("No Warning Message Given. <br />A post cannot be warned without a message");
		}
		$data['is_warned']=1;
		$data['warned_message']=$message;
		$this->setMetaData($data);

		if ($gBitSystem->isPackageActive('messages')) {
			require_once(MESSAGES_PKG_PATH.'Messages.php');

			$u = new BitUser($this->mInfo['user_id']);
			$u->load();
			$userInfo = $u->mInfo;

			$pm = new Messages();
			$message = "Your post \"".$this->mInfo['title']."\" [http://".$_SERVER['HTTP_HOST'].$this->getContactUrl()."] has been warned with the following message:\n$message\n";
			$msgHash = array(
				'to_login' => $userInfo['login'],
				'to'       => $userInfo['real_name'],
				'subject'  => tra( 'Warned Post' ).': '.$this->mInfo['title'],
				'priority' => 4,
			);
			$pm->postMessage( $msgHash );
		}
	}

	function setMetaData($data) {
		if ($this->isValid()) {
			$key = array('comment_id' => $this->mCommentId);
			$query_sel = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."boards_posts` WHERE comment_id=?";
			$c = $this->mDb->getOne( $query_sel , array_values($key));
			if ($c == 0) {
				$data=array_merge($data,$key);
				$this->mDb->associateInsert(BIT_DB_PREFIX."boards_posts",$data);
			} else {

				$this->mDb->associateUpdate(BIT_DB_PREFIX."boards_posts",$data,$key);
			}
		}
	}
}
?>
