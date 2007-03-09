{strip}
<table class="data">
	{foreach item=board from=$boardsList}
		{if $board.title}
			{assign var=board_title value=$board.title|escape}
		{else}
			{assign var=board_title value=$board.content_id|escape}
			{assign var=board_title value="(Content $board_title)"}
		{/if}

		<tr class="{cycle values="even,odd"}{if $board.unreg > 0} unapproved{/if}">
			<td style="width:1px">
			{* topic tracking icons *}
				{if $board.track.on && $board.track.mod}
					{biticon ipackage="icons" iname="folder-new" ipath="large" iexplain="New Posts" iforce="icon"}
				{else}
					{biticon ipackage="icons" iname="folder" ipath="large" iexplain="New Posts" iforce="icon"}
				{/if}
					<strong class="count">{$board.post_count}</strong>
			</td>
			<td>
				<h2 class="title"><a href="{$board.url}" title="{$board_title}">{$board_title}</a></h2>
				<span style="float:right; text-align:right">
					{if !empty($board.last)}
						Last Post:&nbsp;
						"<a href="{$board.last.url}" title="{$board.last.title|default:"Post..."}">{$board.last.title|default:"Post..."}</a>"&nbsp;
						<br/>{if $board.last.last_modified > 0}{$board.last.last_modified|reltime}{/if}
						&nbsp;by&nbsp;
						{if $board.last.user_id < 0}{$board.last.l_anon_name|escape}{else}{displayname user_id=$board.last.user_id}{/if}
					{/if}
					{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
						{if $board.unreg > 0}<a class="highlight" href="{$board.url}" title="{$board.title|escape}">{$board.unreg}</a>{/if}
					{/if}
				</span>
				<div class="desc">
					{$board.parsed_data}
				</div>
			</td>
		</tr>
	{/foreach}
</table>
{/strip}
