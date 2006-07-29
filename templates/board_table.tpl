{strip}
	<table class="mb-table">
		<tr>
			<th width="1">{if $boardsList.0.track.on}<small>UBSI</small>{/if}</th>
			<th style="text-align:left;white-space: nowrap;">Title</th>
			{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
				<th style="text-align: center;">Anon</th>
			{/if}
			<th style="text-align: center;">Topics</th>
			<th style="text-align: center;">Last Topic</th>
			<th style="text-align: center;">Poster</th>
			<th style="text-align: right;">Updated</th>
		</tr>
		{foreach item=board from=$boardsList}
			{if $board.title}
				{assign var=board_title value=$board.title|escape}
			{else}
				{assign var=board_title value=$board.content_id|escape}
				{assign var=board_title value="(Content $board_title)"}
			{/if}
			{cycle values="even,odd" print=false assign=cycle_var}
			<tr class="{$cycle_var} {if $board.unreg > 0} mb-{$cycle_var}-unapproved{/if}">
				<td class="actionicon" width="1px">{* topic tracking icons *}
					{if $board.track.on && $board.track.mod}
						{biticon ipackage=bitboard iname="track_new_l" iexplain="New Posts"}
					{elseif $board.track.on}
						{biticon ipackage=bitboard iname="track_old_l" iexplain="No New Posts"}
					{/if}
				</td>
				<td><a href="{$board.url}" title="{$board_title}">{$board_title}</a><div style="margin-left:2em;" class="small">{$board.parsed_data}</blockquote></td>
				{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
					<td style="text-align:center;">{if $board.unreg > 0}<a style="color: blue;" href="{$board.url}" title="{$board.title|escape}">{$board.unreg}</a>{/if}</td>
				{/if}
				<td style="text-align:center;">{if $board.post_count > 0}{$board.post_count}</a>{/if}</td>
				{if !empty($board.last)}
					<td style="text-align:center;"><a href="{$board.last.url}">{$board.last.title}</td>
					<td style="text-align:center;">{if $board.last.user_id < 0}{$board.last.l_anon_name|escape}{else}{displayname user_id=$board.last.user_id}{/if}</td>
					<td style="text-align:right;">{if $board.last.last_modified > 0}{$board.last.last_modified|reltime}</a>{/if}</td>
				{else}
					<td></td>
					<td></td>
					<td></td>
				{/if}
			</tr>
		{/foreach}
	</table>
{/strip}