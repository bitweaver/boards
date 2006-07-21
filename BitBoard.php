<?php
/**
* $Header: /cvsroot/bitweaver/_bit_boards/BitBoard.php,v 1.4 2006/07/21 23:58:44 hash9 Exp $
* $Id: BitBoard.php,v 1.4 2006/07/21 23:58:44 hash9 Exp $
*/

/**
* BitBoard class to illustrate best practices when creating a new bitweaver package that
* builds on core bitweaver functionality, such as the Liberty CMS engine
*
* @date created 2004/8/15
* @author spider <spider@steelsun.com>
* @version $Revision: 1.4 $ $Date: 2006/07/21 23:58:44 $ $Author: hash9 $
* @class BitBoard
*/

require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );

/**
* This is used to uniquely identify the object
*/
define( 'BITFORUM_CONTENT_TYPE_GUID', 'bitforum' );

class BitBoard extends LibertyAttachable {
	/**
	* Primary key for our mythical BitBoard class object & table
	* @public
	*/
	var $mBitBoardId;

	/**
	* During initialisation, be sure to call our base constructors
	**/
	function BitBoard( $pBitBoardId=NULL, $pContentId=NULL ) {
		LibertyAttachable::LibertyAttachable();
		$this->mBitBoardId = $pBitBoardId;
		$this->mContentId = $pContentId;
		$this->mContentTypeGuid = BITFORUM_CONTENT_TYPE_GUID;
		$this->registerContentType( BITFORUM_CONTENT_TYPE_GUID, array(
		'content_type_guid' => BITFORUM_CONTENT_TYPE_GUID,
		'content_description' => 'Forum Board',
		'handler_class' => 'BitBoard',
		'handler_package' => 'bitboards',
		'handler_file' => 'BitBoard.php',
		'maintainer_url' => 'http://www.bitweaver.org'
		) );
	}

	/**
	* Load the data from the database
	* @param pParamHash be sure to pass by reference in case we need to make modifcations to the hash
	**/
	function load() {
		if( $this->verifyId( $this->mBitBoardId ) || $this->verifyId( $this->mContentId ) ) {
			// LibertyContent::load()assumes you have joined already, and will not execute any sql!
			// This is a significant performance optimization
			$lookupColumn = $this->verifyId( $this->mBitBoardId ) ? 'board_id' : 'content_id';
			$bindVars = array();
			$selectSql = $joinSql = $whereSql = '';
			array_push( $bindVars, $lookupId = @BitBase::verifyId( $this->mBitBoardId ) ? $this->mBitBoardId : $this->mContentId );
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$query = "SELECT s.*, lc.*, " .
			"uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name, " .
			"uuc.`login` AS creator_user, uuc.`real_name` AS creator_real_name " .
			"$selectSql " .
			"FROM `".BIT_DB_PREFIX."forum_board` s " .
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
				$this->mInfo['parsed_data'] = $this->parseData();

				LibertyAttachable::load();
			}
		}
		return( count( $this->mInfo ) );
	}

	/**
	* Any method named Store inherently implies data will be written to the database
	* @param pParamHash be sure to pass by reference in case we need to make modifcations to the hash
	* This is the ONLY method that should be called in order to store( create or update )an bitforum!
	* It is very smart and will figure out what to do for you. It should be considered a black box.
	*
	* @param array pParams hash of values that will be used to store the page
	*
	* @return bool TRUE on success, FALSE if store could not occur. If FALSE, $this->mErrors will have reason why
	*
	* @access public
	**/
	function store( &$pParamHash ) {
		if( $this->verify( $pParamHash )&& LibertyAttachable::store( $pParamHash ) ) {
			$table = BIT_DB_PREFIX."forum_board";
			$this->mDb->StartTrans();
			if( $this->mBitBoardId ) {
				$locId = array( "board_id" => $pParamHash['board_id'] );
				$result = $this->mDb->associateUpdate( $table, $pParamHash['bitforum_store'], $locId );
			} else {
				$pParamHash['bitforum_store']['content_id'] = $pParamHash['content_id'];
				if( @$this->verifyId( $pParamHash['board_id'] ) ) {
					// if pParamHash['board_id'] is set, some is requesting a particular board_id. Use with caution!
					$pParamHash['bitforum_store']['board_id'] = $pParamHash['board_id'];
				} else {
					$pParamHash['bitforum_store']['board_id'] = $this->mDb->GenID( 'forum_board_id_seq' );
				}
				$this->mBitBoardId = $pParamHash['bitforum_store']['board_id'];

				$result = $this->mDb->associateInsert( $table, $pParamHash['bitforum_store'] );
				$result = $this->mDb->associateInsert( BIT_DB_PREFIX."forum_map",array('board_content_id'=>$pParamHash['bitforum_store']['content_id'],'topic_content_id'=>$pParamHash['bitforum_store']['content_id']));
			}


			$this->mDb->CompleteTrans();
			$this->load();
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
		if( @$this->verifyId( $pParamHash['content_type_guid'] ) ) {
			$pParamHash['content_type_guid'] = $this->mContentTypeGuid;
		}

		if( @$this->verifyId( $pParamHash['content_id'] ) ) {
			$pParamHash['bitforum_store']['content_id'] = $pParamHash['content_id'];
		}

		// check some lengths, if too long, then truncate
		if( $this->isValid() && !empty( $this->mInfo['description'] ) && empty( $pParamHash['description'] ) ) {
			// someone has deleted the description, we need to null it out
			$pParamHash['bitforum_store']['description'] = '';
		} else if( empty( $pParamHash['description'] ) ) {
			unset( $pParamHash['description'] );
		} else {
			$pParamHash['bitforum_store']['description'] = substr( $pParamHash['description'], 0, 200 );
		}

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
	* This function removes a bitforum entry
	**/
	function expunge() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "DELETE FROM `".BIT_DB_PREFIX."forum_map` WHERE `board_content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );
			$query = "DELETE FROM `".BIT_DB_PREFIX."forum_board` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );
			if( LibertyAttachable::expunge() ) {
				$ret = TRUE;
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return $ret;
	}

	/**
	* Make sure bitforum is loaded and valid
	**/
	function isValid() {
		return( $this->verifyId( $this->mBitBoardId ) );
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
		$ret['umap'] = $b->getUnMapped();
		return $ret;
	}

	function getUnMapped() {
		global $gBitSystem;
		$ret = NULL;
		$sql = "SELECT
			lc.`title`,
			lc.`content_id`,
			lct.`content_description`,
			( SELECT count(*)
				FROM `".BIT_DB_PREFIX."liberty_comments` lcom
				WHERE lcom.`root_id`=lcom.`parent_id` AND lcom.`root_id`=lc.`content_id`
				) AS thread_count
			FROM `".BIT_DB_PREFIX."liberty_content` lc
			INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON (lc.`content_type_guid`=lct.`content_type_guid`)
			WHERE lc.`content_id` NOT IN (
				SELECT	lc.`content_id` AS content_id
					FROM `".BIT_DB_PREFIX."forum_board` b
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` blc ON (blc.`content_id`=b.`content_id`)
					INNER JOIN  `".BIT_DB_PREFIX."forum_map` map ON (map.`board_content_id`= blc.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id`=map.`topic_content_id`)
				)
				AND lc.`content_type_guid` != 'pigeonholes'
				AND lc.`content_type_guid` != 'bitforum'
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
			$this->mDb->associateInsert( BIT_DB_PREFIX."forum_map",$data);
		}
	}

	function removeContent($content_id) {
		if (@BitBase::verifyId($content_id) && @BitBase::verifyId($this->mContentId)) {
			$sql = "DELETE FROM `".BIT_DB_PREFIX."forum_map` WHERE `board_content_id` = ? AND `topic_content_id` = ?";
			$result = $this->mDb->query( $sql, array( $this->mContentId,$content_id ) );
		}
	}

	function verifyIntegrity() {
		global $gBitSystem;
		$ret = false;
		if( $this->isValid() ) {
			$sql = "SELECT
				COUNT(*)
				FROM `".BIT_DB_PREFIX."forum_board` b
				INNER JOIN  `".BIT_DB_PREFIX."forum_map` map ON (map.`board_content_id`= b.`content_id`)
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
			$sql = "SELECT `board_content_id` FROM `".BIT_DB_PREFIX."forum_map` map WHERE map.`topic_content_id`=?";
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
			blc.`title` AS b_title,
			blc.`content_id` AS b_content_id,
			b.`board_id` AS b_board_id,
						( SELECT count(*)
				FROM `".BIT_DB_PREFIX."liberty_comments` lcom
				WHERE lcom.`root_id`=lcom.`parent_id` AND lcom.`root_id`=lc.`content_id`
				) AS thread_count,
			((blc.`content_id`- lc.`content_id`)*(blc.`content_id`- lc.`content_id`)) AS order_key
					FROM `".BIT_DB_PREFIX."forum_board` b
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` blc ON (blc.`content_id`=b.`content_id`)
					INNER JOIN  `".BIT_DB_PREFIX."forum_map` map ON (map.`board_content_id`= blc.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id`=map.`topic_content_id`)
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

	/**
	* This function generates a list of records from the liberty_content database for use in a list page
	**/
	function getList( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		// this makes sure parameters used later on are set
		LibertyContent::prepGetList( $pParamHash );

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
		$track = $gBitSystem->isFeatureActive('bitboards_thread_track');
		$track = true;
		if ($track) {
			$selectSql .= ", (
					SELECT COUNT(trk.`topic_id`)
					FROM `".BIT_DB_PREFIX."forum_map` AS map
					INNER JOIN `".BIT_DB_PREFIX."liberty_comments` lcom ON (map.`topic_content_id` = lcom.`root_id`)
					INNER JOIN `".BIT_DB_PREFIX."forum_tracking` trk ON (trk.`topic_id` = lcom.`thread_forward_sequence`)
					WHERE lcom.`root_id`=lcom.`parent_id` AND map.`board_content_id`=lc.`content_id` AND trk.`user_id`=".$gBitUser->mUserId."
				) AS track_count ";

		}

		$query = "SELECT ts.*, lc.`content_id`, lc.`title`, lc.`data`,
			( SELECT count(*)
				FROM `".BIT_DB_PREFIX."forum_map` AS map
				INNER JOIN `".BIT_DB_PREFIX."liberty_comments` lcom ON (map.`topic_content_id` = lcom.`root_id`)
				WHERE lcom.`root_id`=lcom.`parent_id` AND map.`board_content_id`=lc.`content_id`
				) AS post_count
			 $selectSql
			FROM `".BIT_DB_PREFIX."forum_board` ts INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id` = ts.`content_id` ) $joinSql
			WHERE lc.`content_type_guid` = ? $whereSql
			ORDER BY ".$this->mDb->convert_sortmode( $sort_mode );
		$query_cant = "select count(*)
				FROM `".BIT_DB_PREFIX."forum_board` ts INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id` = ts.`content_id` ) $joinSql
			WHERE lc.`content_type_guid` = ? $whereSql";
		$result = $this->mDb->query( $query, $bindVars, $max_records, $offset );
		$ret = array();
		while( $res = $result->fetchRow() ) {
			$res['url']= BITBOARDS_PKG_URL."index.php?b={$res['board_id']}";
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
			}
			$ret[] = $res;
		}
		$pParamHash["cant"] = $this->mDb->getOne( $query_cant, $bindVars );

		// add all pagination info to pParamHash
		LibertyContent::postGetList( $pParamHash );
		return $ret;
	}

	/**
	* Generates the URL to the bitforum page
	* @param pExistsHash the hash that was returned by LibertyContent::pageExists
	* @return the link to display the page.
	*/
	function getDisplayUrl() {
		$ret = NULL;
		if( @$this->verifyId( $this->mBitBoardId ) ) {
			$ret = BITBOARDS_PKG_URL."index.php?b=".$this->mBitBoardId;
		}
		return $ret;
	}
}
?>
