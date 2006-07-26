{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/board.tpl,v 1.5 2006/07/26 22:45:30 hash9 Exp $ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="listing bitboard">
	<div class="header">
	<h1>Forums</h1>
	</div>

	<div class="body">
		{minifind sort_mode=$sort_mode b=$smarty.request.b}
		{form id="checkform"}
			<input type="hidden" name="offset" value="{$control.offset|escape}" />
			<input type="hidden" name="sort_mode" value="{$control.sort_mode|escape}" />

			<table class="mb-table">

				{foreach item=board from=$boardsList}
				{if $board.title}
					{assign var=board_title value=$board.title|escape}
				{else}
					{assign var=board_title value=$board.content_id|escape}
					{assign var=board_title value="(Content $board_title)"}
				{/if}
				{cycle values="even,odd" print=false assign=cycle_var}
				<tr class="{$cycle_var} {if $board.unreg > 0} mb-{$cycle_var}-unapproved{/if}">
					<td class="actionicon" width="1px">{* thread tracking icons *}
						{if $board.track.on && $board.track.mod}
							{biticon ipackage=bitboard iname="track_new_l" iexplain="New Posts"}
						{elseif $board.track.on}
							{biticon ipackage=bitboard iname="track_old_l" iexplain="No New Posts"}
						{/if}
					</td>
						<td><a href="{$board.url}" title="{$board_title}">{$board_title}</a></td>

					{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
					<td style="text-align:right;">{if $board.unreg > 0}<a style="color: blue;" href="{$board.url}" title="{$board.title|escape}">{$board.unreg}&nbsp;Unregistered&nbsp;Posts</a>{/if}</td>{/if}
					<td style="text-align:right; color: blue;">{if $board.post_count > 0}{$board.post_count}&nbsp;Threads</a>{/if}</td>
					</tr>
				{foreachelse}
					<tr class="norecords"><td colspan="16">
						{tr}No records found{/tr}
					</td></tr>
				{/foreach}
			</table>
			{*
			{if $gBitUser->hasPermission( 'p_bitboards_remove' )}
				<div style="text-align:right;">
					<script type="text/javascript">/* <![CDATA[ check / uncheck all */
					document.write("<label for=\"switcher\">{tr}Select All{/tr}</label> ");
					document.write("<input name=\"switcher\" id=\"switcher\" type=\"checkbox\" onclick=\"switchCheckboxes(this.form.id,'checked[]','switcher')\" /><br />");
					/* ]]> */</script>

					<select name="submit_mult" onchange="this.form.submit();">
						<option value="" selected="selected">{tr}with checked{/tr}:</option>
						{if $gBitUser->hasPermission( 'p_bitboards_remove' )}
							<option value="remove_bitboards">{tr}remove{/tr}</option>
						{/if}
					</select>

					<noscript><div><input type="submit" value="{tr}Submit{/tr}" /></div></noscript>
				</div>
			{/if}
			*}
		{/form}

		{pagination b=$smarty.request.b}
	</div><!-- end .body -->
</div><!-- end .admin -->
{/strip}
