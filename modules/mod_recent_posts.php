<?php
/**
 * Params: 
 * - title : if is "title", show the title of the post, else show the date of creation
 * - b : numeric id of board to show posts from
 * - all_boards : display posts from all boards. Default behavior is to auto-track to board you are in.
 *
 * @version $Header$
 * @package boards
 * @subpackage modules
 */

/**
 * required setup
 */

include_once( BOARDS_PKG_CLASS_PATH.'BitBoardPost.php' );

global $gBitSmarty, $gQueryUserId, $gBitSystem, $moduleParams;
if( !empty( $moduleParams ) ) {
	extract( $moduleParams );
}

$listHash = array( 'user_id' => $gQueryUserId, 'sort_mode' => 'created_desc' );

if( !empty( $moduleParams['module_rows'] ) ) {
	$listHash['max_records'] = $moduleParams['module_rows'];
}

if( !empty( $module_params['b'] ) ) {
	$listHash['board_id'] = $module_params['b'];
} elseif( !empty( $_REQUEST['b'] ) && empty( $module_params['all_boards'] ) ) {
	$listHash['board_id'] = $_REQUEST['b'];
}
$_template->tpl_vars['modRecentPostsBoardId'] = new Smarty_variable( !empty( $listHash['board_id'] ) );

if( BitBase::verifyId( $gQueryUserId ) ) {
	$listHash['user_id'] = $gQueryUserId;
}

$post = new BitBoardPost();
if( $postList = $post->getList( $listHash ) ) {
	$_template->tpl_vars['modLastBoardPosts'] = new Smarty_variable( $postList );
}

?>
