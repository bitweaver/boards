{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/board.tpl,v 1.3 2006/07/12 16:57:33 hash9 Exp $ *}
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
					<tr class="mb-row-{cycle values="even,odd"}{if $board.unreg > 0}-unapproved{/if}">
					{*<td  width="1px">

							<a href="{$smarty.const.BIT_ROOT_URL}index.php?content_id={$board.content_id|escape:"url"}" title="Show: $board_title">{biticon ipackage=liberty iname="view" iexplain="Show: $board_title"}</a>
							</td>*}
						<td><a href="{$board.url}" title="{$board_title}">{$board_title}</a></td>
						{*
					{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
					<td style="text-align:right;">{if $board.unreg > 0}<a style="color: blue;" href="{$smarty.const.MESSAGEBOARDS_PKG_URL}index.php?board_id={$board.board_id|escape:"url"}" title="{$board.title|escape}">{$board.unreg}&nbsp;Unregistered&nbsp;Posts</a>{/if}</td>{/if}
						{if $gBitUser->hasPermission( 'p_bitboards_remove' )}
							<td class="actionicon">
								{smartlink ititle="Edit" ifile="edit.php" ibiticon="liberty/edit" board_id=$board.board_id}
								<input type="checkbox" name="checked[]" title="{$board.title|escape}" value="{$board.bitboards_id}" />
							</td>
							{/if}
							*}
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
