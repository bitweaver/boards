<?php
/**
 * Params: 
 * - title : if is "title", show the title of the post, else show the date of creation
 * - b : numeric id of board to show posts from
 * - all_boards : display posts from all boards. Default behavior is to auto-track to board you are in.
 *
 * @version $Header: /cvsroot/bitweaver/_bit_boards/modules/mod_recent_posts.php,v 1.4 2007/03/09 22:27:35 spiderr Exp $
 * @package boards
 * @subpackage modules
 */

/**
 * required setup
 */

include_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );

global $gBitSmarty, $gQueryUserId, $module_rows, $module_params, $gBitSystem;

$listHash = array( 'user_id' => $gQueryUserId, 'sort_mode' => 'created_desc', 'max_records' => $module_rows );
if( !empty( $module_params['b'] ) ) {
	$listHash['board_id'] = $module_params['b'];
} elseif( !empty( $_REQUEST['b'] ) && empty( $module_params['all_boards'] ) ) {
	$listHash['board_id'] = $_REQUEST['b'];
}
$gBitSmarty->assign( 'modRecentPostsBoardId', !empty( $listHash['board_id'] ) ? $listHash['board_id'] : '' );

if( BitBase::verifyId( $gQueryUserId ) ) {
	$listHash['user_id'] = $gQueryUserId;
}

$post = new BitBoardPost();
if( $postList = $post->getList( $listHash ) ) {
	$gBitSmarty->assign('modLastBoardPosts', $postList );
}

$gBitThemes = new BitThemes();
$modParams = $gBitThemes->getModuleParameters('bitpackage:boards/mod_last_boards_posts.tpl', $gQueryUserId);

?>
