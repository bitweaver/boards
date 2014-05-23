{strip}
<div class="admin boards">
	<div class="header">
		<h1>{tr}Assign Content to Boards{/tr}</h1>
	</div>

	<div class="body">
		{if $data.umap}
			{form legend="Assign Content"}
				<div class="control-group">
					{formlabel label="Add Content" for="assign"}
					{forminput}
						<select id="assign" name="assign[]" multiple="multiple" size="12">
							{foreach item=umapped key=content_name from=$data.umap}
								<optgroup label="{$content_name}">
									{foreach item=umapping from=$umapped}
										<option value="{$umapping.content_id}">{$umapping.title|truncate:30} [{$umapping.thread_count}]</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
						{formhelp note="All comments posted to the selected content will show up on this board."}
					{/forminput}
				</div>

				<div class="control-group">
					{formlabel label="Assign to Board" for="to_board_id"}
					{forminput}
						<select name="to_board_id" id="to_board_id">
							{foreach item=board from=$data.map name='board_loop'}
								<option value="{$board.board_id}">{$board.title|escape}</option>
							{/foreach}
						</select>
						{formhelp note="All comments posted to the selected content will show up on this board."}
					{/forminput}
				</div>

				<div class="control-group submit">
					<input type="submit" class="btn btn-default" value="{tr}Assign Content to Board{/tr}" name="action" />
				</div>
			{/form}
		{else}
			{formfeedback success="No Unassigned Content"}
		{/if}

		{foreach item=board from=$data.map name='board_loop'}
			{if $board.map}
				{capture assign=title}
					{if ! $board.integrity}
						{booticon iname="icon-warning-sign"   iexplain="Integrity Check Failed"}
					{/if}
					{$board.title|escape}
				{/capture}

				{form legend="<a href=\"`$board.url`\">`$board.title`</a>" id="board`$smarty.foreach.board_loop.iteration`"}
					{if ! $board.integrity}
						<div class="floaticon">
							<a href="{$smarty.const.BOARDS_PKG_URL}assign.php?integrity={$board.board_id}#board{$smarty.foreach.board_loop.iteration}">
								{booticon iname="icon-warning-sign"  ipackage="icons"  ipath="large" iexplain="Fix Integrity"}
							</a>
						</div>
						{formfeedback warning="Integrity Check Failed"}
					{/if}

					<table class="table data">
						<caption>{tr}Assigned Content{/tr}</caption>
						<tr>
							<th style="width:15%;">{tr}Content Type{/tr}</th>
							<th style="width:55%;">{tr}Content Title{/tr}</th>
							<th style="width:15%;">{tr}Posts{/tr}</th>
							<th style="width:15%;">{tr}Action{/tr}</th>
						</tr>

						{foreach item=mapping from=$board.map}
							<tr class="{cycle values="odd,even"}">
								<td>{$mapping.t_content_name}</td>
								<td>{$mapping.t_title|escape}</td>
								<td style="text-align:right">{$mapping.thread_count}</td>
								<td class="actionicon">
									<input type="checkbox" name="remove[{$board.board_id}][{$mapping.t_content_id}]" value="1" />
									<a title="{tr}Remove from board{/tr}" href="{$smarty.const.BOARDS_PKG_URL}assign.php?remove[{$board.board_id}][{$mapping.t_content_id}]=1#board{$smarty.foreach.board_loop.iteration}">{booticon iname="icon-trash" ipackage="icons" iexplain="Remove from board"}</a>
								</td>
							</tr>
						{/foreach}
					</table>

					<div class="control-group submit">
						<input type="submit" class="btn btn-default" value="{tr}Remove{/tr}" name="action" />
					</div>
				{/form}
			{/if}
		{/foreach}
	</div><!-- end .body -->
</div><!-- end .boards -->
{/strip}
