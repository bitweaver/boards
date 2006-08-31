{if $gBitUser->hasPermission('p_bitboards_link_content')}
<div class="row">
	{formlabel label="Linked Board"}
	{forminput}
		{if $boardList}
			{html_options name="linked_board_cid" options=$boardList selected=$boardInfo.board_content_id}
		{else}
			<em>{tr}No discussion boards have been created.{/tr}</em>
		{/if}
		{formhelp note="Comments added will appear on the selected message board."}
	{/forminput}
</div>
{/if}
