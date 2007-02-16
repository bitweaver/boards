{strip}
<table class="data">
	{if $heading}
		<tr>
			<th style="width:1%;">{*if $boardsList.0.track.on}Status{/if*}</th>
			<th style="width:35%;">{tr}Board{/tr}</th>
			<th style="width:5%;">{tr}Topics{/tr}</th>
			<th style="width:40%;">{tr}Last Post{/tr}</th>
			<th style="width:18%;">&nbsp;</th>
			{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
				<th style="width:1%;"><abbr title="{tr}Number of posts by Anonymous users{/tr}">Anon</abbr></th>
			{/if}
		</tr>
	{/if}

	{foreach item=board from=$boardsList}
		{if $board.title}
			{assign var=board_title value=$board.title|escape}
		{else}
			{assign var=board_title value=$board.content_id|escape}
			{assign var=board_title value="(Content $board_title)"}
		{/if}

		<tr class="{cycle values="even,odd"}{if $board.unreg > 0} unapproved{/if}">
			<td style="width:1px;">{* topic tracking icons *}
				{if $board.track.on && $board.track.mod}
					{biticon ipackage="icons" iname="folder-new" ipath="large" iexplain="New Posts" iforce="icon"}
				{else}
					{biticon ipackage="icons" iname="folder" ipath="large" iexplain="New Posts" iforce="icon"}
				{/if}
			</td>

			<td>
				<h3><a href="{$board.url}" title="{$board_title}">{$board_title}</a></h3>
				{$board.parsed_data}
			</td>

			<td style="text-align:center;">{if $board.post_count > 0}{$board.post_count}{/if}</td>

			{if !empty($board.last)}
				<td>
					<a href="{$board.last.url}">{$board.last.title|default:"Post..."}</a>
				</td>
			{else}
				<td> </td>
			{/if}

			{if !empty($board.last)}
				<td style="text-align:center;">
					{if $board.last.last_modified > 0}{$board.last.last_modified|reltime}{/if}<br/>
					{if $board.last.user_id < 0}{$board.last.l_anon_name|escape}{else}{displayname user_id=$board.last.user_id}{/if}
				</td>
			{else}
				<td> </td>
			{/if}

			{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
				<td style="text-align:center;">
					{if $board.unreg > 0}<a class="highlight" href="{$board.url}" title="{$board.title|escape}">{$board.unreg}</a>{/if}
				</td>
			{/if}
		</tr>
	{/foreach}
</table>
{/strip}
