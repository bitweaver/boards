<?php
/* mass-remove:
the checkboxes are sent as the array $_REQUEST["checked[]"], values are the wiki-PageNames,
e.g. $_REQUEST["checked"][3]="HomePage"
$_REQUEST["submit_mult"] holds the value of the "with selected do..."-option list
we look if any page's checkbox is on and if remove_boards is selected.
then we check permission to delete boards.
if so, we call histlib's method remove_all_versions for all the checked boards.
*/
if( isset( $_REQUEST["submit_mult"] ) && isset( $_REQUEST["checked"] ) && $_REQUEST["submit_mult"] == "remove_boards" ) {

	// Now check permissions to remove the selected bitboard
	$gContent->verifyPermission( 'p_boards_remove' );
	$gBitUser->verifyTicket();

	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['b'] = $_REQUEST['b'];
		$formHash['delete'] = TRUE;
		$formHash['submit_mult'] = 'remove_boards';
		foreach( $_REQUEST["checked"] as $del ) {
			$formHash['input'][] = '<input type="hidden" name="checked[]" value="'.$del.'"/>';
		}
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete '.count( $_REQUEST["checked"] ).' Topics?', 'error' => 'This cannot be undone!' ) );
	} else {
		foreach( $_REQUEST["checked"] as $deleteId ) {
			$deleteComment = new LibertyComment( $deleteId );
			if( $deleteComment->isValid() && $gBitUser->hasPermission('p_liberty_admin_comments') ) {
				if( !$deleteComment->deleteComment() ) {
					$gBitSmarty->assign_by_ref( 'errors', $deleteComment->mErrors );
				}
			}
		}
		if( !empty( $errors ) ) {
			$gBitSmarty->assign_by_ref( 'errors', $errors );
		}
	}
} elseif( isset( $_REQUEST['remove'] ) && BitBase::verifyId( $_REQUEST['thread_id'] ) ) {
	$gBitUser->verifyTicket();
	$tmpTopic = new BitBoardTopic( $_REQUEST['thread_id'] );
	$tmpTopic->load();
	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['b'] = $_REQUEST['b'];
		$formHash['remove'] = TRUE;
		$formHash['thread_id'] = $_REQUEST['thread_id'];
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => tra( 'Are you sure you want to delete the topic' ).' "'.$tmpTopic->getTitle().'" ?', 'error' => 'This cannot be undone!' ) );
	} else {
		$deleteComment = new LibertyComment($_REQUEST['thread_id']);
		if( $deleteComment->isValid() && $gBitUser->hasPermission('p_liberty_admin_comments') ) {
			if( !$deleteComment->deleteComment() ) {
				$gBitSmarty->assign_by_ref( 'errors', $deleteComment->mErrors );
			}
		}
	}
	
}
?>
