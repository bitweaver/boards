<div class="floaticon">{bithelp}</div>

<div class="admin bitforum">
	<div class="header">
		<h1>
			{tr}Assign Content to Boards{/tr}
		</h1>
	</div>
	<div class="body">
		{jstabs tab=$smarty.request.tab}
			{foreach item=board from=$data.map name='board_loop'}
				{capture assign=title}
					<span style="font-size: 1.3em;">
					{if ! $board.integrity}
						<img src="{$smarty.const.LIBERTY_PKG_URL}/icons/warning.png" alt="Integrity Check Failed" title="Integrity Check Failed" class="icon" />
					{/if}
						{$board.title}
					</span>
				{/capture}
				{jstab title=$title}
					{form legend="<a href=\"`$board.url`\">`$board.title`</a>"}
						<input type="hidden" name="tab" value="{$smarty.foreach.board_loop.iteration-1}" />
						{if ! $board.integrity}
							<div class="floaticon">
								<a href="{$smarty.const.BITBOARDS_PKG_URL}assign.php?tab={$smarty.foreach.board_loop.iteration-1}&integrity={$board.board_id}">
									{biticon ipackage=bitboards iname="db_update" iexplain="Fix Integrity"}
								</a>
							</div>
							{formfeedback warning="Integrity Check Failed"}
						{/if}
						<table cellpadding="2" cellspacing="2">
							{foreach item=mapping from=$board.map}
								<tr>
									<td width="1"><a title="{tr}Remove from board{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}assign.php?tab={$smarty.foreach.board_loop.iteration-1}&remove[{$board.board_id}][{$mapping.t_content_id}]=1">{biticon ipackage=liberty iname="delete" iexplain="Remove from board"}</a></td>
									<td width="1"><input type="checkbox" name="remove[{$board.board_id}][{$mapping.t_content_id}]" value="1" /></td>
									<td width="1" style="font-style: italic; color: blue;">{$mapping.thread_count}</td>
									<td>{$mapping.t_title}</td>
								</tr>
							{/foreach}
							<tr>
								<td></td>
								<td colspan="4">
									<select name="assign[{$board.board_id}][]">
										{foreach item=umapping from=$data.umap}
											<option value="{$umapping.content_id}">{$umapping.title} ({$umapping.content_description}) [{$umapping.thread_count}]</option>
										{/foreach}
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="4" style="text-align: center;"><input type="submit" value="Add" name="action" /> <input type="submit" value="Remove" name="action" /></td>
							</tr>
						</table>
					{/form}
				{/jstab}
			{/foreach}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .bitforum -->