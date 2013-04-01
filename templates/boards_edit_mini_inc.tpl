{if $gBitUser->hasPermission('p_boards_link_content') && $gContent->mContentTypeGuid != $smarty.const.BITBOARD_CONTENT_TYPE_GUID}
{* {$gContent->mContentTypeGuid} *}
<div class="control-group">
	{formlabel label="Linked Board"}
	{forminput}
		{if $boardList}
			{if $smarty.post.preview}
				{html_options name="linked_board_cid" options=$boardList selected=$smarty.post.linked_board_cid}
			{else}
				{html_options name="linked_board_cid" options=$boardList selected=$boardInfo.board_content_id}
			{/if}
		{else}
			<em>{tr}No discussion boards have been created.{/tr}</em>
		{/if}
		{formhelp note="Comments added will appear on the selected message board."}
	{/forminput}
</div>
{/if}
