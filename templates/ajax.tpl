{strip}
	<option value="">Select a Board</option>
	{foreach from=$boardList item=board}
		<option value="{$board.content_id}"> {$board.title|escape} [{$board.post_count}]</option>
	{/foreach}
{/strip}
