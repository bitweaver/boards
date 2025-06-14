<?php
/**
 * $Header$
 * $Id$
 *
 * BitBoard class to illustrate best practices when creating a new bitweaver package that
 * builds on core bitweaver functionality, such as the Liberty CMS engine
 *
 * @author spider <spider@steelsun.com>
 * @version $Revision$
 * @package boards
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_CLASS_PATH.'LibertyMime.php' );

/**
* This is used to uniquely identify the object
*/
define( 'BITBOARD_CONTENT_TYPE_GUID', 'bitboard' );

/**
 * @package boards
 */
class BitBoard extends LibertyMime {
	/**
	* Primary key for our mythical BitBoard class object & table
	* @public
	*/
	public $mBitBoardId;

	/**
	* During initialisation, be sure to call our base constructors
	**/
	function __construct( $pBitBoardId=NULL, $pContentId=NULL ) {
		parent::__construct();
		$this->mBitBoardId = (int)$pBitBoardId;
		$this->mContentId = (int)$pContentId;
		$this->mContentTypeGuid = BITBOARD_CONTENT_TYPE_GUID;
		$this->registerContentType( BITBOARD_CONTENT_TYPE_GUID, array(
				'content_type_guid' => BITBOARD_CONTENT_TYPE_GUID,
				'content_name' => 'Message Board',
				'handler_class' => 'BitBoard',
				'handler_package' => 'boards',
				'handler_file' => 'BitBoard.php',
				'maintainer_url' => 'http://www.bitweaver.org'
		));

		// Permission setup
		$this->mViewContentPerm  = 'p_boards_read';
		$this->mCreateContentPerm  = 'p_boards_create';
		$this->mUpdateContentPerm  = 'p_boards_update';
		$this->mAdminContentPerm = 'p_boards_admin';
		$this->mExpungeContentPerm = 'p_boards_remove';
	}

	/**
	* Load the data from the database
	* @param pParamHash be sure to pass by reference in case we need to make modifcations to the hash
	**/
	function load( $pContentId = NULL, $pPluginParams = NULL ) {
		if( $this->verifyId( $this->mBitBoardId ) || $this->verifyId( $this->mContentId ) ) {
			// LibertyContent::load()assumes you have joined already, and will not execute any sql!
			// This is a significant performance optimization
			$lookupColumn = $this->verifyId( $this->mBitBoardId ) ? 'board_id' : 'content_id';
			$bindVars = array();
			$selectSql = $joinSql = $whereSql = '';
			array_push( $bindVars, (int)($lookupId = @BitBase::verifyId( $this->mBitBoardId ) ? $this->mBitBoardId : $this->mContentId) );
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$query = "SELECT s.*, lc.*, " .
			"uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name, " .
			"uuc.`login` AS creator_user, uuc.`real_name` AS creator_real_name " .
			"$selectSql " .
			"FROM `".BIT_DB_PREFIX."boards` s " .
			"INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id` = s.`content_id` ) $joinSql" .
			"LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON( uue.`user_id` = lc.`modifier_user_id` )" .
			"LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON( uuc.`user_id` = lc.`user_id` )" .
			"WHERE s.`$lookupColumn`=? $whereSql";
			$result = $this->mDb->query( $query, $bindVars );

			if( $result && $result->numRows() ) {
				$this->mInfo = $result->fields;
				$this->mContentId = $result->fields['content_id'];
				$this->mBitBoardId = $result->fields['board_id'];

				$this->mInfo['creator'] =( isset( $result->fields['creator_real_name'] )? $result->fields['creator_real_name'] : $result->fields['creator_user'] );
				$this->mInfo['editor'] =( isset( $result->fields['modifier_real_name'] )? $result->fields['modifier_real_name'] : $result->fields['modifier_user'] );
				$this->mInfo['display_url'] = $this->getDisplayUrl();
				$this->parseData();

				LibertyMime::load();
			}
		}
		return( count( $this->mInfo ) );
	}

	public static function lookupByMigrateBoard( $pMigrateBoardId ) {
		global $gBitDb;
		$ret = NULL;
		if( BitBase::verifyId( $pMigrateBoardId ) ) {
			$ret = $gBitDb->getOne( "SELECT `board_id` FROM `".BIT_DB_PREFIX."boards` bb WHERE `migrate_board_id`=?", array( $pMigrateBoardId ) );
		}
		return $ret;
	}

	/**
	* Any method named Store inherently implies data will be written to the database
	* @param pParamHash be sure to pass by reference in case we need to make modifcations to the hash
	* This is the ONLY method that should be called in order to store( create or update )an bitboard!
	* It is very smart and will figure out what to do for you. It should be considered a black box.
	*
	* @param array pParams hash of values that will be used to store the page
	*
	* @return bool TRUE on success, FALSE if store could not occur. If FALSE, $this->mErrors will have reason why
	*
	* @access public
	**/
	function store( &$pParamHash ) {
		if( $this->verify( $pParamHash )&& LibertyMime::store( $pParamHash ) ) {
			$table = BIT_DB_PREFIX."boards";
			$this->StartTrans();
			if( $this->mBitBoardId ) {
				$locId = array( "board_id" => $pParamHash['board_id'] );
				$result = $this->mDb->associateUpdate( $table, $pParamHash['board_store'], $locId );
			} else {
				$pParamHash['board_store']['content_id'] = $pParamHash['content_id'];
				if( @$this->verifyId( $pParamHash['board_id'] ) ) {
					// if pParamHash['board_id'] is set, some is requesting a particular board_id. Use with caution!
					$pParamHash['board_store']['board_id'] = $pParamHash['board_id'];
				} else {
					$pParamHash['board_store']['board_id'] = $this->mDb->GenID( 'boards_board_id_seq' );
				}
				$this->mBitBoardId = $pParamHash['board_store']['board_id'];

				$result = $this->mDb->associateInsert( $table, $pParamHash['board_store'] );
				$result = $this->mDb->associateInsert( BIT_DB_PREFIX."boards_map",array('board_content_id'=>$pParamHash['board_store']['content_id'],'topic_content_id'=>$pParamHash['board_store']['content_id']));
				if( !empty( $pParamHash['boards_mailing_list'] ) ) {
					global $gBitSystem, $gBitUser;
					require_once( UTIL_PKG_INCLUDE_PATH.'mailman_lib.php' );
					if( $gBitSystem->getConfig( 'boards_sync_mail_server' ) ) {
						if( !($error = mailman_newlist( array( 'listname' => $pParamHash['boards_mailing_list'], 'listhost' => $gBitSystem->getConfig( 'boards_email_host', $gBitSystem->getConfig( 'kernel_server_name' ) ), 'admin-password'=>$pParamHash['boards_mailing_list_password'], 'listadmin-addr'=>$gBitUser->getField( 'email' ) ) )) ) {
							$this->storePreference( 'boards_mailing_list', !empty( $pParamHash['boards_mailing_list'] ) ? $pParamHash['boards_mailing_list'] : NULL );
							$this->storePreference( 'boards_mailing_list_password', $pParamHash['boards_mailing_list_password'] );
							// Subscribe the owner
							mailman_addmember( $this->getPreference( 'boards_mailing_list'), $gBitUser->getField('email') );
							// If we have an inbox then subscribe it as a moderator
							if( $this->getBoardSyncInbox() ) {
								mailman_addmember( $this->getPreference( 'boards_mailing_list' ), $this->getBoardSyncInbox() );
								mailman_setmoderator( $this->getPreference( 'boards_mailing_list' ), $this->getBoardSyncInbox() );
							}
							$this->storePreference( 'board_sync_list_address', $this->getBoardMailingList() );
						} else {
							$this->mErrors['mailing_list'] = $error;
						}
					}
				}
			}

			if( count( $this->mErrors ) == 0 ) {
				$this->CompleteTrans();
				$this->load();
			} else {
				$this->mDb->RollbackTrans();
				$this->mContentId = NULL;
				$this->mBitBoardId = NULL;
			}
		}
		return( count( $this->mErrors )== 0 );
	}

	/**
	* Make sure the data is safe to store
	* @param pParamHash be sure to pass by reference in case we need to make modifcations to the hash
	* This function is responsible for data integrity and validation before any operations are performed with the $pParamHash
	* NOTE: This is a PRIVATE METHOD!!!! do not call outside this class, under penalty of death!
	*
	* @param array pParams reference to hash of values that will be used to store the page, they will be modified where necessary
	*
	* @return bool TRUE on success, FALSE if verify failed. If FALSE, $this->mErrors will have reason why
	*
	* @access private
	**/
	function verify( &$pParamHash ) {
		// make sure we're all loaded up of we have a mBitBoardId
		if( $this->verifyId( $this->mBitBoardId ) && empty( $this->mInfo ) ) {
			$this->load();
		}

		if( @$this->verifyId( $this->mInfo['content_id'] ) ) {
			$pParamHash['content_id'] = $this->mInfo['content_id'];
		}

		// It is possible a derived class set this to something different
		if( empty( $pParamHash['content_type_guid'] ) ) {
			$pParamHash['content_type_guid'] = $this->mContentTypeGuid;
		}

		if( @$this->verifyId( $pParamHash['content_id'] ) ) {
			$pParamHash['board_store']['content_id'] = $pParamHash['content_id'];
		}

		if( @$this->verifyId( $pParamHash['migrate_board_id'] ) ) {
			$pParamHash['board_store']['migrate_board_id'] = $pParamHash['migrate_board_id'];
		}
/* board description seems to have been removed in favor of liberty_content data
		// check some lengths, if too long, then truncate
		if( $this->isValid() && !empty( $this->mInfo['description'] ) && empty( $pParamHash['description'] ) ) {
			// someone has deleted the description, we need to null it out
			$pParamHash['board_store']['description'] = '';
		} else if( empty( $pParamHash['description'] ) ) {
			unset( $pParamHash['description'] );
		} else {
			$pParamHash['board_store']['description'] = substr( $pParamHash['description'], 0, 200 );
		}
*/
		if( !empty( $pParamHash['data'] ) ) {
			$pParamHash['edit'] = $pParamHash['data'];
		}

		// check for name issues, first truncate length if too long
		if( !empty( $pParamHash['title'] ) ) {
			if( empty( $this->mBitBoardId ) ) {
				if( empty( $pParamHash['title'] ) ) {
					$this->mErrors['title'] = 'You must enter a name for this page.';
				} else {
					$pParamHash['content_store']['title'] = substr( $pParamHash['title'], 0, 160 );
				}
			} else {
				$pParamHash['content_store']['title'] =( isset( $pParamHash['title'] ) )? substr( $pParamHash['title'], 0, 160 ): '';
			}
		} else if( empty( $pParamHash['title'] ) ) {
			// no name specified
			$this->mErrors['title'] = 'You must specify a name';
		}

		return( count( $this->mErrors )== 0 );
	}


	/**
	 * Prepare data for preview
	 */
	function preparePreview( $pParamHash ) {
		global $gBitSystem, $gBitUser;

		if( empty( $pParamHash['user_id'] ) ) {
			$pParamHash['user_id'] = $gBitUser->mUserId;
		}

		if( isset( $pParamHash["title"] ) ) {
			$this->mInfo["title"] = $pParamHash["title"];
		}

		if( isset( $pParamHash["description"] ) ) {
			$this->mInfo["description"] = $pParamHash["description"];
		}

		if( isset( $pParamHash["format_guid"] ) ) {
			$this->mInfo['format_guid'] = $pParamHash["format_guid"];
		}

		if( isset( $pParamHash["edit"] ) ) {
			$this->mInfo["data"] = $pParamHash["edit"];
			$this->parseData();
		}
	}


	/**
	* This function removes a bitboard entry
	**/
	function expunge() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->StartTrans();
			$mailingList = $this->getPreference( 'boards_mailing_list' );
			$query = "DELETE FROM `".BIT_DB_PREFIX."boards_map` WHERE `board_content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );
			$query = "DELETE FROM `".BIT_DB_PREFIX."boards` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );
			if( LibertyMime::expunge() ) {
				if( $mailingList ) {
					require_once( UTIL_PKG_INCLUDE_PATH.'mailman_lib.php' );
					if( $error = mailman_rmlist( $mailingList ) ) {
						$this->mErrors['mailing_list'] = $error;
					}
				}
				$ret = TRUE;
				$this->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return $ret;
	}

	/**
	* Make sure bitboard is loaded and valid
	**/
	function isValid() {
		return( $this->verifyId( $this->mBitBoardId ) && $this->verifyId( $this->mContentId ) );
	}

	function getAllMap() {
		$b = new BitBoard();
		$listHash = array();
		$l = $b->getList($listHash);
		$ret = array();
		foreach ($l as $k => $boardd) {
			$board = new BitBoard($boardd['board_id']);
			$board->mInfo=$boardd;
			$ret['map'][$k]=$boardd;
			$ret['map'][$k]['map'] = $board->getMap();
			$ret['map'][$k]['integrity'] = $board->verifyIntegrity();
		}
		// reorganise unmapped content for better display
		$umapped = $b->getUnMapped();
		foreach( $umapped as $key => $content ) {
			$umap[$content['content_name']][$key] = $content;
		}
		$ret['umap'] = $umap;
		return $ret;
	}

	function getUnMapped() {
		global $gBitSystem;
		$ret = NULL;
		$sql = "SELECT
			lc.`title`,
			lc.`content_id`,
			lct.`content_name`, (
			SELECT count(*)
				FROM `".BIT_DB_PREFIX."liberty_comments` lcom
				WHERE lcom.`root_id`=lcom.`parent_id` AND lcom.`root_id`=lc.`content_id`
			) AS thread_count
			FROM `".BIT_DB_PREFIX."liberty_content` lc
				INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON (lc.`content_type_guid`=lct.`content_type_guid`)
			WHERE lc.`content_id` NOT IN (
				SELECT	lc.`content_id` AS content_id
					FROM `".BIT_DB_PREFIX."boards` b
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` blc ON (blc.`content_id`=b.`content_id`)
					INNER JOIN  `".BIT_DB_PREFIX."boards_map` map ON (map.`board_content_id`= blc.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id`=map.`topic_content_id`)
				)
				AND lc.`content_type_guid` != 'pigeonholes'
				AND lc.`content_type_guid` != 'bitboard'
				AND lc.`content_type_guid` != 'bitcomment'
				AND lc.`title` != ''
			ORDER BY lc.`content_type_guid`, lc.`title`
			";
		$rs = $this->mDb->query( $sql );
		while( $row = $rs->fetchRow() ) {
			$ret[$row['content_id']] = $row;
		}
		return $ret;
	}

	function addContent($content_id) {
		if (@BitBase::verifyId($content_id)) {
			$data = array(
			'board_content_id'=>$this->mContentId,
			'topic_content_id'=>$content_id,
			);
			$this->mDb->associateInsert( BIT_DB_PREFIX."boards_map",$data);
		}
	}

	function removeContent($content_id) {
		if (@BitBase::verifyId($content_id) && @BitBase::verifyId($this->mContentId)) {
			$sql = "DELETE FROM `".BIT_DB_PREFIX."boards_map` WHERE `board_content_id` = ? AND `topic_content_id` = ?";
			$result = $this->mDb->query( $sql, array( $this->mContentId,$content_id ) );
		}
	}

	function verifyIntegrity() {
		global $gBitSystem;
		$ret = false;
		if( $this->isValid() ) {
			$sql = "SELECT
				COUNT(*)
				FROM `".BIT_DB_PREFIX."boards` b
				INNER JOIN  `".BIT_DB_PREFIX."boards_map` map ON (map.`board_content_id`= b.`content_id`)
				WHERE b.`board_id`=? AND map.`board_content_id` = map.`topic_content_id`
			";
			$count = $this->mDb->getOne( $sql, array( $this->mBitBoardId ));
			return ($count==1);
		}
		return $ret;
	}

	function fixContentMap() {
		if( $this->isValid() && @BitBase::verifyId($this->mContentId)) {
			$this->removeContent($this->mContentId);
			$this->addContent($this->mContentId);
		}
	}

	function lookupMapRev($content_id) {
		global $gBitDb;
		$ret = NULL;
		if (@BitBase::verifyId($content_id)) {
			$sql = "SELECT `board_content_id` FROM `".BIT_DB_PREFIX."boards_map` map WHERE map.`topic_content_id`=?";
			$ret = $gBitDb->getOne( $sql, array( $content_id ));
		}
		return $ret;
	}

	function getMap() {
		global $gBitSystem;
		$ret = NULL;
		if( $this->isValid() ) {
			$sql = "SELECT
			lc.`title` AS t_title,
			lc.`content_id` AS t_content_id,
			lct.`content_name` AS t_content_name,
			blc.`title` AS b_title,
			blc.`content_id` AS b_content_id,
			b.`board_id` AS b_board_id, (
				SELECT count(*)
					FROM `".BIT_DB_PREFIX."liberty_comments` lcom
					WHERE lcom.`root_id`=lcom.`parent_id` AND lcom.`root_id`=lc.`content_id`
				) AS thread_count,
			((blc.`content_id`- lc.`content_id`)*(blc.`content_id`- lc.`content_id`)) AS order_key
					FROM `".BIT_DB_PREFIX."boards` b
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` blc ON (blc.`content_id`=b.`content_id`)
					INNER JOIN  `".BIT_DB_PREFIX."boards_map` map ON (map.`board_content_id`= blc.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id`=map.`topic_content_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON (lc.`content_type_guid`=lct.`content_type_guid`)
					WHERE b.`board_id`=? AND map.`board_content_id`!=map.`topic_content_id`
					ORDER BY order_key
					";
			$rs = $this->mDb->query( $sql, array( $this->mBitBoardId ));
			while( $row = $rs->fetchRow() ) {
				$ret[$row['t_content_id']] = $row;
			}
		}
		return $ret;
	}

	public static function prepGetList( &$pParamHash ) {
		if( empty( $pParamHash['sort_mode'] ) ) {
			// default sort_mode for boards is alphabetical
			$pParamHash['sort_mode'] = 'title_asc';
		}
		LibertyContent::prepGetList( $pParamHash );
	}

	/**
	* This function generates a list of records from the liberty_content database for use in a list page
	**/
	function getList( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		// this makes sure parameters used later on are set
		$this->prepGetList( $pParamHash );

		$selectSql = $joinSql = $whereSql = '';
		$bindVars = array();
		array_push( $bindVars, $this->mContentTypeGuid );
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		// this will set $find, $sort_mode, $max_records and $offset
		extract( $pParamHash );

		if( is_array( $find ) ) {
			// you can use an array of pages
			$whereSql .= " AND lc.`title` IN( ".implode( ',',array_fill( 0,count( $find ),'?' ) )." )";
			$bindVars = array_merge ( $bindVars, $find );
		} elseif( is_string( $find ) ) {
			// or a string
			$whereSql .= " AND UPPER( lc.`title` )like ? ";
			$bindVars[] = '%' . strtoupper( $find ). '%';
		}

		$pagination=true;
		if (!empty($pParamHash['paginationOff'])) {
			$pagination=false;
		}

		if (!empty($pParamHash['boards']) && is_array($pParamHash['boards'])) {
			$whereSql .= " AND lc.`content_id` IN ( ".implode( ',',array_fill( 0,count( $pParamHash['boards'] ),'?' ) )." )";
			$bindVars = array_merge ( $bindVars, $pParamHash['boards'] );
		}
		if (!empty($pParamHash['nboards']) && is_array($pParamHash['nboards'])) {
			$whereSql .= " AND lc.`content_id` NOT IN ( ".implode( ',',array_fill( 0,count( $pParamHash['nboards'] ),'?' ) )." )";
			$bindVars = array_merge ( $bindVars, $pParamHash['nboards'] );
		}

		$track = $gBitSystem->isFeatureActive('boards_thread_track');
		$track = true;
		if ($track) {
			$selectSql .= ", (
					SELECT COUNT(trk.`topic_id`)
					FROM `".BIT_DB_PREFIX."boards_map` map
					INNER JOIN `".BIT_DB_PREFIX."liberty_comments` lcom ON (map.`topic_content_id` = lcom.`root_id`)
					INNER JOIN `".BIT_DB_PREFIX."boards_tracking` trk ON (trk.`topic_id` = lcom.`thread_forward_sequence`)
					WHERE lcom.`root_id`=lcom.`parent_id` AND map.`board_content_id`=lc.`content_id` AND trk.`user_id`=".$gBitUser->mUserId."
				) AS track_count ";

		}

		if ($gBitSystem->isFeatureActive('boards_posts_anon_moderation') && !($gBitUser->hasPermission('p_boards_update') || $gBitUser->hasPermission('p_boards_post_update'))) {

		}
		if ($gBitSystem->isFeatureActive('boards_posts_anon_moderation') && ($gBitUser->hasPermission('p_boards_update') || $gBitUser->hasPermission('p_boards_post_update'))) {
			$selectSql .= ", ( SELECT COUNT(*)
			FROM `".BIT_DB_PREFIX."boards_map` map
			INNER JOIN `".BIT_DB_PREFIX."liberty_comments` s_lcom ON (map.`topic_content_id` = s_lcom.`root_id`)
			INNER JOIN `".BIT_DB_PREFIX."liberty_content` s_lc ON (s_lcom.`content_id` = s_lc.`content_id`)
			LEFT JOIN  `".BIT_DB_PREFIX."boards_posts` s ON( s_lcom.`comment_id` = s.`comment_id` )
WHERE map.`board_content_id`=lc.`content_id` AND ((s_lc.`user_id` < 0) AND (s.`is_approved` = 0 OR s.`is_approved` IS NULL) )
			) AS unreg";
		} else {
			$selectSql .= ", 0 AS unreg";
		}

		// always show our own content
		$whereSql .= " AND ( lc.`user_id`= ? ";
		$bindVars[] = $gBitUser->mUserId;
		if( !empty( $pParamHash['content_status_id'] ) ) {
			// TODO needs safety checking! - spider
			$whereSql .= " OR lc.`content_status_id` = ? ";
			$bindVars[] =  $pParamHash['content_status_id'];
		} elseif( !empty( $pParamHash['min_content_status_id'] ) ) {
			$whereSql .= " OR lc.`content_status_id` >= ? ";
			$bindVars[] =  $pParamHash['min_content_status_id'];
		}
		$whereSql .= " ) ";

		$query = "SELECT ts.*, lc.`content_id`, lc.`title`, lc.`data`, lc.`format_guid`
			 $selectSql
			FROM `".BIT_DB_PREFIX."boards` ts
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id` = ts.`content_id` ) $joinSql
			WHERE lc.`content_type_guid` = ? $whereSql
			ORDER BY ".$this->mDb->convertSortmode( $sort_mode );

		$query_cant = "select count(*)
			FROM `".BIT_DB_PREFIX."boards` ts INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id` = ts.`content_id` ) $joinSql
			WHERE lc.`content_type_guid` = ? $whereSql";

		$result = $this->mDb->query( $query, $bindVars );

		$ret = array();
		while( $res = $result->fetchRow() ) {
			$res['url']= BOARDS_PKG_URL."index.php?b={$res['board_id']}";

			$res['topic_count'] = $this->mDb->getOne( "SELECT count(*)
				FROM `".BIT_DB_PREFIX."boards_map` map
					INNER JOIN `".BIT_DB_PREFIX."liberty_comments` lcom ON (map.`topic_content_id` = lcom.`root_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` slc ON( slc.`content_id` = lcom.`content_id` )
					LEFT JOIN `".BIT_DB_PREFIX."boards_posts` fp ON (fp.`comment_id` = lcom.`comment_id`)
				WHERE lcom.`root_id`=lcom.`parent_id` AND map.`board_content_id`=? AND ((fp.`is_approved` = 1) OR (fp.`is_approved` IS NULL))", array( $res['content_id'] ) );

			$res['post_count'] = $this->mDb->getOne( "SELECT count(*)
				FROM `".BIT_DB_PREFIX."liberty_comments` lcom
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` slc ON( slc.`content_id` = lcom.`content_id` )
					INNER JOIN `".BIT_DB_PREFIX."boards_map` map ON (lcom.`root_id`=map.`topic_content_id`)
					LEFT JOIN `".BIT_DB_PREFIX."boards_posts` fp ON (fp.`comment_id` = lcom.`comment_id`)
				WHERE map.`board_content_id`=? AND ((fp.`is_approved` = 1) OR (fp.`is_approved` IS NULL))", array( $res['content_id'] ) );
			if($track) {
				if ($gBitUser->isRegistered()) {
					$res['track']['on'] = true;
					$res['track']['count'] = $res['track_count'];
					if ($res['post_count']>$res['track_count']) {
						$res['track']['mod'] = true;
					} else {
						$res['track']['mod'] = false;
					}
				}  else {
					$res['track']['on'] = false;
				}
				unset($res['track_count']);
				$res['parsed_data'] = LibertyContent::parseDataHash( $res );
				$res['last'] = $this->getLastTopic($res);
			}
			$ret[] = $res;
		}
		$pParamHash["cant"] = $this->mDb->getOne( $query_cant, $bindVars );

		// add all pagination info to pParamHash
		LibertyContent::postGetList( $pParamHash );
		return $ret;
	}

	function getLastTopic($data) {
		global $gBitSystem;
		$BIT_DB_PREFIX = BIT_DB_PREFIX;
		$query="SELECT slc.`last_modified`, slc.`user_id`, lcom.`anon_name` AS l_anon_name, slc.`title`, lcom.comment_id AS topic_id
			FROM `".BIT_DB_PREFIX."boards_map` map
				INNER JOIN `".BIT_DB_PREFIX."liberty_comments` lcom ON (map.`topic_content_id` = lcom.`root_id`)
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` slc ON( slc.`content_id` = lcom.`content_id` )
				LEFT JOIN `".BIT_DB_PREFIX."boards_posts` fp ON (fp.`comment_id` = lcom.`comment_id`)
			WHERE lcom.`root_id`=lcom.`parent_id` AND map.`board_content_id`=? AND ((fp.`is_approved` IS NULL OR fp.`is_approved` = 1) OR (slc.`user_id` >= 0))
		    ORDER BY slc.`last_modified` DESC
	    ";
		$result = $this->mDb->getRow( $query, array( $data['content_id'] ) );
		if (!empty($result['topic_id'])) {
			if (empty($result['l_anon_name'])) {
				$result['l_anon_name'] = "Anonymous";
			}
			$result['topic_id']=intval($result['topic_id']);
			$result['url'] = BitBoardTopic::getDisplayUrlFromHash( $result );
		}
		return $result;
	}

	/**
	* Generates the URL to the bitboard page
	* @return the link to display the page.
	*/
	public static function getDisplayUrlFromHash( &$pParamHash ) {
		global $gBitSystem;
		$ret = NULL;

		if( !empty( $pParamHash['comment'] ) && !empty( $pParamHash['comment']['thread_forward_sequence'] ) ){
			// look up base of comment sequece which is BitBoardTopic
			$seq = explode( ".",  $pParamHash['comment']['thread_forward_sequence'] );
			$topicRootId = 	(int)$seq[0];
			if( BitBase::verifyId( $topicRootId )) {
				require_once( BOARDS_PKG_CLASS_PATH.'BitBoardTopic.php' );
				$hash = array( 'topic_id' => $topicRootId );
				$ret = BitBoardTopic::getDisplayUrlFromHash( $hash );
				// we're out of here with our topic url
				return $ret;
			}
		}

		if( BitBase::verifyId( $pParamHash['board_id'] )) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) || $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
				$rewrite_tag = $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ? 'view/':'';
				$ret = BOARDS_PKG_URL.$rewrite_tag.'board/'.$pParamHash['board_id'];
			} else {
				$ret = BOARDS_PKG_URL."index.php?b=".$pParamHash['board_id'];
			}
		} else {
			$ret = parent::getDisplayUrlFromHash( $pParamHash );
		}

		return $ret;
	}

	function getBoardSelectList( $pBlankFirst=FALSE ) {
		global $gBitSystem, $gBitUser;

		// invoke list sql principly to enforce permission services
		$selectSql = $joinSql = $whereSql = '';
		$bindVars = array();

		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		/* DEPRECATED - has some pointless stuff
		$query = "SELECT lc.`content_id` as hash_key, lc.`title` AS `title`
			FROM `".BIT_DB_PREFIX."boards` b
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id` = b.`content_id` )
				LEFT JOIN `".BIT_DB_PREFIX."boards_map` bm ON( bm.`board_content_id`=b.`content_id` )
				LEFT JOIN `".BIT_DB_PREFIX."liberty_comments` lcom ON (bm.`topic_content_id` = lcom.`root_id`)
			GROUP BY lc.`content_id`, lc.`title`, lcom.`comment_id`
			ORDER BY lc.`title` ASC";
		 */

		// replacement query where we at least check that the board has been mapped to another board - a requirement for displaying comments
		// if you hate it fix it or lets chat -wjames5
		$query = "SELECT lc.`content_id` as hash_key, lc.`title` AS `title`
			FROM `".BIT_DB_PREFIX."boards` b
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id` = b.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."boards_map` bm ON( bm.`topic_content_id`=b.`content_id` )
				$joinSql
			WHERE lc.`content_id` = b.`content_id` $whereSql
			ORDER BY lc.`title` ASC";

		$ret = array();

		if( $rslt = $this->mDb->query( $query, $bindVars ) ){
			if( $pBlankFirst ) {
				$ret[''] = '---------';
			}
			while( $row = $rslt->fetchRow() ) {
				$ret[$row['hash_key']] = $row['title'];
			}
		}

		return $ret;
	}

	function getBoard( $contentId ) {
		global $gBitDb;
		global $gBitUser;
		//var_dump($GLOBALS);
		if( LibertyContent::verifyId( $contentId ) ) {
			// LibertyContent::load()assumes you have joined already, and will not execute any sql!
			// This is a significant performance optimization
			$bindVars = array();
			$selectSql = $joinSql = $whereSql = '';
			array_push( $bindVars, $contentId );
			$gBitUser->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$query = "SELECT lc.*, uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name, uuc.`login` AS creator_user, uuc.`real_name` AS creator_real_name $selectSql
			FROM `".BIT_DB_PREFIX."liberty_content` lc $joinSql
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON( uue.`user_id` = lc.`modifier_user_id` )
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON( uuc.`user_id` = lc.`user_id` )
			WHERE lc.`content_id`=? $whereSql";
			$result = $gBitDb->query( $query, $bindVars );

			$ret = array();
			if( $result && $result->numRows() ) {
				$ret = $result->fields;

				$ret['creator'] =( isset( $result->fields['creator_real_name'] )? $result->fields['creator_real_name'] : $result->fields['creator_user'] );
				$ret['editor'] =( isset( $result->fields['modifier_real_name'] )? $result->fields['modifier_real_name'] : $result->fields['modifier_user'] );
				$ret['display_url'] = BIT_ROOT_URL."index.php?content_id=$contentId";
			}
		}
		return( $ret );
	}

	public static function getLinkedBoard( $pContentId ) {
		global $gBitDb;
		$ret = NULL;
		if( BitBase::verifyId( $pContentId ) ) {
			$sql = "SELECT b.`board_id`, b.`content_id` AS `board_content_id`, COUNT(lcom.`comment_id`) AS `post_count`
					FROM `".BIT_DB_PREFIX."boards_map` bm
						INNER JOIN `".BIT_DB_PREFIX."boards` b ON (bm.`board_content_id`=b.`content_id`)
						LEFT JOIN `".BIT_DB_PREFIX."liberty_comments` lcom ON (lcom.`root_id`=bm.`topic_content_id`)
					WHERE bm.`topic_content_id`=?
					GROUP BY b.`board_id`, b.`content_id`";
			$ret = $gBitDb->getRow( $sql, array( $pContentId ) );
		}
		return $ret;
	}


	// =-=-=-=-=-=-=-=-=-=-=-=-=-= Board Sync Methods =-=-=-=-=-=-=-=-=-=-=-=-=-=
	function getBoardSyncInbox() {
		global $gBitSystem;
		$ret = '';
		if( $gBitSystem->getConfig('boards_sync_user') ) {
			$ret = $gBitSystem->getConfig('boards_sync_user').'@'.$gBitSystem->getConfig('boards_sync_mail_server');
		}
		return $ret;
	}

	function getBoardMailingList() {
		global $gBitSystem;
		$ret = NULL;
		if( $this->isValid() && $gBitSystem->getConfig( 'boards_sync_mail_server' ) && $this->getPreference( 'boards_mailing_list' ) ) {
			$ret = $this->getPreference( 'boards_mailing_list' ).'@'.$gBitSystem->getConfig( 'boards_email_host', $gBitSystem->getConfig( 'kernel_server_name' ) );
		}
		return $ret;
	}
}

function boards_content_display ( $pContent ) {
	global $gBitSmarty;
	if( $pContent->isValid() && $pContent->getField('content_type_guid') != 'bitboard' ) {
		$gBitSmarty->assign( 'boardInfo', BitBoard::getLinkedBoard( $pContent->mContentId ) );
	}
}

function boards_content_edit ( $pContent, $pParamHash ) {
	global $gBitSmarty;
	// topic service
	if( !$pContent->isContentType( BITBOARDTOPIC_CONTENT_TYPE_GUID ) ) {
		if( $pContent->isValid() ) {
			$gBitSmarty->assign( 'boardInfo', BitBoard::getLinkedBoard( $pContent->mContentId ) );
		} elseif( !empty( $pParamHash['linked_board_cid'] ) ) {
			$boardInfo['board_content_id'] = $pParamHash['linked_board_cid'];
			$gBitSmarty->assign( 'boardInfo', $boardInfo );
		}
		require_once( BOARDS_PKG_CLASS_PATH.'BitBoard.php' );
		$board = new BitBoard();
		$boardList = $board->getBoardSelectList( TRUE );
		$gBitSmarty->assign( 'boardList', $boardList );
	}
}

// store services for boads
function boards_content_store( $pContent, $pParamHash ) {
	global $gBitDb, $gBitSmarty, $gBitSystem;

	require_once( BOARDS_PKG_CLASS_PATH.'BitBoardTopic.php' );
	// do not allow unassigning topics. the UI should prevent this, but just to make sure...
	if( $pContent->isValid() && !$pContent->isContentType( BITBOARDTOPIC_CONTENT_TYPE_GUID ) && !$pContent->isContentType( BITBOARD_CONTENT_TYPE_GUID ) ) {
		// wipe out all previous assignments for good measure. Not the sanest thing to do, but edits are infrequent - at least for now
		if ($gBitSystem->isFeatureActive('boards_link_by_pigeonholes') && $gBitSystem->isPackageActive('pigeonholes')) {
			// Delete all old mappings
			$pContent->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."boards_map` WHERE `topic_content_id`=?", array( $pContent->mContentId ) );

			// Get the pigeonholes this content is in
			$p = null;
			if ( ! empty( $pParamHash['pigoneholes'] ) ) {
				foreach( $pParamHash['pigeonholes']['pigeonhole'] as $p_id ) {
					require_once(PIGEONHOLES_PKG_CLASS_PATH.'Pigeonholes.php');
					if (empty($p)) {
						$p = new Pigeonholes();
					}

					// What boards are in the same pigeonhole?
					$params =
						array('content_type_guid' => BITBOARD_CONTENT_TYPE_GUID,
							  'content_id' => $p_id );
					$boards = $p->getMemberList( $params );

					// Insert into these boards
					foreach ($boards as $board) {
						if( @BitBase::verifyId( $board['content_id'] ) ) {
							$pContent->mDb->query( "INSERT INTO `".BIT_DB_PREFIX."boards_map` (`board_content_id`,`topic_content_id`) VALUES (?,?)", array( $board['content_id'], $pContent->mContentId ) );
						}
					}
				}
			}
		}
		else {
			$pContent->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."boards_map` WHERE `topic_content_id`=?", array( $pContent->mContentId ) );
			if( @BitBase::verifyId( $pParamHash['linked_board_cid'] ) ) {
				$pContent->mDb->query( "INSERT INTO `".BIT_DB_PREFIX."boards_map` (`board_content_id`,`topic_content_id`) VALUES (?,?)", array( $pParamHash['linked_board_cid'], $pContent->mContentId ) );
			}
		}
		$gBitSmarty->assign( 'boardInfo', BitBoard::getLinkedBoard( $pContent->mContentId ) );
	} else {
		if( @BitBase::verifyId( $pParamHash['content_id'] ) && @BitBase::verifyId( $pParamHash['linked_board_cid'] ) ) {
			$pContent->mDb->query( "INSERT INTO `".BIT_DB_PREFIX."boards_map` (`board_content_id`,`topic_content_id`) VALUES (?,?)", array( $pParamHash['linked_board_cid'], $pParamHash['content_id'] ) );
		}
	}
}

// store services for topics and posts
function boards_comment_store( &$pObject, &$pParamHash ) {
	global $gBitSystem;
	// board posts ( e.g. liberty comments ) service
	// @TODO check that root object is a board -- otherwise all comments get fired
	// @TODO probably should migrate sendNotification to Switchboard

	if( $gBitSystem->isPackageActive( 'boards' ) && $pObject->isContentType( BITCOMMENT_CONTENT_TYPE_GUID ) && $gBitSystem->isFeatureActive( 'boards_thread_notification' )) {
		if( isset( $pObject->mInfo['thread_forward_sequence'] ) ){
			$topic_id = substr( $pObject->mInfo['thread_forward_sequence'], 0, 10 );
			$data = BitBoardTopic::getNotificationData($topic_id);
			foreach( $data['users'] as $login => $user ) {
				if( $data['topic']->mInfo['llc_last_modified'] > $user['track_date'] && $data['topic']->mInfo['llc_last_modified'] > $user['track_notify_date'] ) {
					$data['topic']->sendNotification( $user );
				}
			}
		}
	}
}

function boards_content_verify( $pObject, &$pParamHash ){
	// board posts ( e.g. liberty comments ) service
	global $gBitSystem, $gBitUser;
	// use is_a instead of isContentType( BITCOMMENT_CONTENT_TYPE_GUID ) as isContentType() checks isValid(), and this service method will not properly handle new object stores
	if( $gBitSystem->isPackageActive( 'boards' ) && is_a( $pObject , 'LibertyComment' ) ) {
		if( BitBoardTopic::isLockedMsg( $pParamHash['parent_id'] )) {
			$pObject->mErrors['warning']=tra("The selected Topic is Locked posting is disabled");
		}
	}
}

function boards_content_expunge( $pContent ) {
	global $gBitSmarty;
	if( $pContent->isValid() ) {
		$pContent->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."boards_map` WHERE `topic_content_id`=?", array( $pContent->mContentId ) );
	}
}

?>
