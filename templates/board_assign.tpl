{strip}
<div class="admin bitboard">
	<div class="header">
		<h1>{tr}Assign Content to Boards{/tr}</h1>
	</div>

	<div class="body">
		{if $data.umap}
			{form legend="Assign Content"}
				<div class="row">
					{formlabel label="Add Content" for="assign"}
					{forminput}
						<select id="assign" name="assign[]" multiple="multiple" size="12">
							{foreach item=umapped key=content_description from=$data.umap}
								<optgroup label="{$content_description}">
									{foreach item=umapping from=$umapped}
										<option value="{$umapping.content_id}">{$umapping.title|truncate:30} [{$umapping.thread_count}]</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
						{formhelp note="All comments posted to the selected content will show up on this board."}
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Assign to Board" for="to_board_id"}
					{forminput}
						<select name="to_board_id" id="to_board_id">
							{foreach item=board from=$data.map name='board_loop'}
								<option value="{$board.board_id}">{$board.title}</option>
							{/foreach}
						</select>
						{formhelp note="All comments posted to the selected content will show up on this board."}
					{/forminput}
				</div>

				<div class="row submit">
					<input type="submit" value="{tr}Assign Content to Board{/tr}" name="action" />
				</div>
			{/form}
		{else}
			{formfeedback success="No Unassigned Content"}
		{/if}

		{foreach item=board from=$data.map name='board_loop'}
			{if $board.map}
				{capture assign=title}
					{if ! $board.integrity}
						<img src="{$smarty.const.LIBERTY_PKG_URL}/icons/warning.png" alt="Integrity Check Failed" title="Integrity Check Failed" class="icon" />
					{/if}
					{$board.title}
				{/capture}

				{form legend="<a href=\"`$board.url`\">`$board.title`</a>" id="board`$smarty.foreach.board_loop.iteration`"}
					{if ! $board.integrity}
						<div class="floaticon">
							<a href="{$smarty.const.BITBOARDS_PKG_URL}assign.php?integrity={$board.board_id}#board{$smarty.foreach.board_loop.iteration}">
								{biticon ipackage="icons" iname="dialog-warning" ipath="large" iexplain="Fix Integrity"}
							</a>
						</div>
						{formfeedback warning="Integrity Check Failed"}
					{/if}

					<table class="data">
						<caption>{tr}Assigned Content{/tr}</caption>
						<tr>
							<th style="width:15%;">{tr}Content Type{/tr}</th>
							<th style="width:55%;">{tr}Content Title{/tr}</th>
							<th style="width:15%;">{tr}Posts{/tr}</th>
							<th style="width:15%;">{tr}Action{/tr}</th>
						</tr>

						{foreach item=mapping from=$board.map}
							<tr class="{cycle values="odd,even"}">
								<td>{$mapping.t_content_description}</td>
								<td>{$mapping.t_title}</td>
								<td style="text-align:right">{$mapping.thread_count}</td>
								<td class="actionicon">
									<input type="checkbox" name="remove[{$board.board_id}][{$mapping.t_content_id}]" value="1" />
									<a title="{tr}Remove from board{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}assign.php?remove[{$board.board_id}][{$mapping.t_content_id}]=1#board{$smarty.foreach.board_loop.iteration}">{biticon ipackage="icons" iname="edit-delete" iexplain="Remove from board"}</a>
								</td>
							</tr>
						{/foreach}
					</table>

					<div class="row submit">
						<input type="submit" value="{tr}Remove{/tr}" name="action" />
					</div>
				{/form}
			{/if}
		{/foreach}
	</div><!-- end .body -->
</div><!-- end .bitboard -->
{/strip}
