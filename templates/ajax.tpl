{strip}
	<option value="">Select a Board</option>
	{foreach from=$boardList item=board}
		<option value="{$board.content_id}"> {$board.title} ({$board.post_count}) ({$board.description})</option>
	{/foreach}
{/strip}