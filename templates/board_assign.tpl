{strip}
<div class="admin bitboard">
	<div class="header">
		<h1>
			{tr}Assign Content to Boards{/tr}
		</h1>
	</div>
	<div class="body">
		{if count($data.map)<5}
			{jstabs}
				{foreach item=board from=$data.map name='board_loop'}
					{capture assign=title}
						{if ! $board.integrity}
							<img src="{$smarty.const.LIBERTY_PKG_URL}/icons/warning.png" alt="Integrity Check Failed" title="Integrity Check Failed" class="icon" />
						{/if}
						{$board.title}
					{/capture}

					{jstab title=$title}
						{form legend="<a href=\"`$board.url`\">`$board.title`</a>"}
							<input type="hidden" name="tab" value="{$smarty.foreach.board_loop.iteration-1}" />
							{if ! $board.integrity}
								<div class="floaticon">
									<a href="{$smarty.const.BITBOARDS_PKG_URL}assign.php?tab={$smarty.foreach.board_loop.iteration-1}&amp;integrity={$board.board_id}">
										{biticon ipackage=liberty iname="warning_large" iexplain="Fix Integrity"}
									</a>
								</div>
								{formfeedback warning="Integrity Check Failed"}
							{/if}

							<div class="row">
								{formlabel label="Add Content" for=""}
								{forminput}
									<select name="assign[{$board.board_id}][]">
										{foreach item=umapping from=$data.umap}
											<option value="{$umapping.content_id}">{$umapping.title|truncate:30} ({$umapping.content_description}) [{$umapping.thread_count}]</option>
										{/foreach}
									</select>
									{formhelp note="All comments posted to the selected content will show up on this board."}
								{/forminput}
							</div>

							<div class="row submit">
								<input type="submit" value="{tr}Add{/tr}" name="action" />
							</div>

							{if $board.map}
								<table class="data">
									<tr>
										<th style="width:60%;">{tr}Content Title{/tr}</th>
										<th style="width:20%;">{tr}Posts{/tr}</th>
										<th style="width:20%;">{tr}Action{/tr}</th>
									</tr>

									{foreach item=mapping from=$board.map}
									<tr class="{cycle values="odd,even"}">
											<td>{$mapping.t_title}</td>
											<td style="text-align:right">{$mapping.thread_count}</td>
											<td class="actionicon">
												<input type="checkbox" name="remove[{$board.board_id}][{$mapping.t_content_id}]" value="1" />
												<a title="{tr}Remove from board{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}assign.php?tab={$smarty.foreach.board_loop.iteration-1}&amp;remove[{$board.board_id}][{$mapping.t_content_id}]=1">{biticon ipackage=liberty iname="delete" iexplain="Remove from board"}</a>
											</td>
										</tr>
									{/foreach}
								</table>

								<div class="row submit">
									<input type="submit" value="{tr}Remove{/tr}" name="action" />
								</div>
							{/if}
						{/form}
					{/jstab}
				{/foreach}
			{/jstabs}
		{else}
			{foreach item=board from=$data.map name='board_loop'}
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
								{biticon ipackage=liberty iname="warning_large" iexplain="Fix Integrity"}
							</a>
						</div>
						{formfeedback warning="Integrity Check Failed"}
					{/if}

					<div class="row">
						{formlabel label="Add Content" for=""}
						{forminput}
							<select name="assign[{$board.board_id}][]">
								{foreach item=umapping from=$data.umap}
									<option value="{$umapping.content_id}">{$umapping.title|truncate:30} ({$umapping.content_description}) [{$umapping.thread_count}]</option>
								{/foreach}
							</select>
							{formhelp note="All comments posted to the selected content will show up on this board."}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" value="{tr}Add{/tr}" name="action" />
					</div>

					{if $board.map}
						<table class="data">
							<tr>
								<th style="width:60%;">{tr}Content Title{/tr}</th>
								<th style="width:20%;">{tr}Posts{/tr}</th>
								<th style="width:20%;">{tr}Action{/tr}</th>
							</tr>

							{foreach item=mapping from=$board.map}
							<tr class="{cycle values="odd,even"}">
									<td>{$mapping.t_title}</td>
									<td style="text-align:right">{$mapping.thread_count}</td>
									<td class="actionicon">
										<input type="checkbox" name="remove[{$board.board_id}][{$mapping.t_content_id}]" value="1" />
										<a title="{tr}Remove from board{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}assign.php?remove[{$board.board_id}][{$mapping.t_content_id}]=1#board{$smarty.foreach.board_loop.iteration}">{biticon ipackage=liberty iname="delete" iexplain="Remove from board"}</a>
									</td>
								</tr>
							{/foreach}
						</table>

						<div class="row submit">
							<input type="submit" value="{tr}Remove{/tr}" name="action" />
						</div>
					{/if}
				{/form}
			{/foreach}
		{/if}
	</div><!-- end .body -->
</div><!-- end .bitboard -->
{/strip}
