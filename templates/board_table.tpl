{strip}
<table class="data">
	<tr>
		<th class="boarddesc" colspan="2">Board</th>
		<th class="topiccount">Topics</th>
		<th class="postcount">Posts</th>
		<th class="lastpost">Last Post</th>
	<tr>
	{foreach item=board from=$boardsList}
		{if $board.title}
			{assign var=board_title value=$board.title|escape}
		{else}
			{assign var=board_title value=$board.content_id|escape}
			{assign var=board_title value="(Content $board_title)"}
		{/if}

		<tr class="{cycle values="even,odd"}{if $board.unreg > 0} unapproved{/if}">
			<td>
			{* topic tracking icons *}
				<span style="float:left;">
					{if $board.track.on && $board.track.mod}
						{biticon ipackage="icons" iname="folder-new" ipath="large" iexplain="New Posts" iforce="icon"}
					{else}
						{biticon ipackage="icons" iname="folder" ipath="large" iexplain="New Posts" iforce="icon"}
					{/if}
				</span>
			</td>
			<td>
				<h2 class="title"><a href="{$board.url}" title="{$board_title}">{$board_title}</a></h2>
				<div class="desc">
					{$board.parsed_data}
				</div>
			</td>
			<td style="text-align:center">
				<strong class="count">{$board.topic_count}</strong>
			</td>
			<td style="text-align:center">
				<strong class="count">{$board.post_count}</strong>
			</td>
			<td>
				{if !empty($board.last)}
					<a href="{$board.last.url}" title="{$board.last.title|default:"Post..."}">{$board.last.title|default:"Post..."|truncate:30}</a>
					<br/>
					on&nbsp;{if $board.last.last_modified > 0}{$board.last.last_modified|reltime}{/if}
					<br/>
					by&nbsp;
					{if $board.last.user_id < 0}{$board.last.l_anon_name|escape}{else}{displayname user_id=$board.last.user_id}{/if}
				{/if}
				{if $gBitUser->hasPermission('p_boards_update') || $gBitUser->hasPermission('p_boards_post_update')}
					{if $board.unreg > 0}<a class="highlight" href="{$board.url}" title="{$board.title|escape}">{$board.unreg}</a>{/if}
				{/if}
			</td>
		</tr>
	{/foreach}
</table>
{/strip}
