{* $Header: /cvsroot/bitweaver/_bit_boards/templates/list_topics.tpl,v 1.19 2008/04/21 20:08:21 wjames5 Exp $ *}
{strip}
<div class="listing boards">
	<div class="floaticon">
		{if $print_page ne 'y'}
			{if $board->getPreference('boards_mailing_list') || $board->hasAdminPermission()}
				<a class="item" href="{$smarty.const.BOARDS_PKG_URL}mailing_list.php?b={$board->mInfo.board_id}" title="{tr}Message Board Mailing List{/tr}">{biticon ipackage="icons" iname="internet-mail" iexplain="Edit Message Board"}</a>
			{/if}
			{if $gBitUser->hasPermission( 'p_boards_edit' )}
				<a title="{tr}Edit message board{/tr}" href="{$smarty.const.BOARDS_PKG_URL}edit.php?b={$board->mInfo.board_id}">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit Message Board"}</a>
			{/if}
			{if $gBitUser->hasPermission( 'p_boards_remove' )}
				<a title="{tr}Remove message board{/tr}" href="{$smarty.const.BOARDS_PKG_URL}board.php?remove=1&amp;b={$board->mInfo.board_id}">{biticon ipackage="icons" iname="edit-delete" iexplain="Remove Message Board"}</a>
			{/if}
		{/if}<!-- end print_page -->
	</div><!-- end .floaticon -->

	<div class="header">
		<h1>{$board->mInfo.title|escape|default:"Message Board Topic"}</h1>
		{if $board->mInfo.parsed_data}
			<p>{$board->mInfo.parsed_data}</p>
		{/if}
		Back to <a href="{$cat_url}">{$board->mInfo.content_type.content_description}s</a>
	</div>

	<div class="body">
		{minifind sort_mode=$sort_mode b=$smarty.request.b}
		{* looks horrible, but leave for now - xing *}
		{if $board->hasPostCommentsPermission()}
			<div class="navbar">
				<a title="{tr}New Topic{/tr}" href="{$comments_return_url}&amp;post_comment_request=1#editcomments">{biticon ipackage="icons" iname="mail-message-new" iexplain="New Topic"}</a>
			</div>
		{/if}

		{form id="checkform"}
			<input type="hidden" name="board_id" value="{$smarty.request.board_id}" />
			<input type="hidden" name="offset" value="{$control.offset|escape}" />
			<input type="hidden" name="sort_mode" value="{$control.sort_mode|escape}" />

			<table class="data">
				{if !$gBitSystem->isFeatureActive('boards_thread_verbrose')}
					<tr>
						<th style="width:5%;"> </th>
						<th style="width:40%;">{tr}Title{/tr}</th>
						<th style="width:5%;">{tr}Replies{/tr}</th>
						<th style="width:20%;">{tr}Started{/tr}</th>
						<th style="width:20%;">{tr}Last Reply{/tr}</th>
						{if $gBitUser->hasPermission('p_boards_edit') || $gBitUser->hasPermission('p_boards_post_edit')}
							<th style="width:1%;"><abbr title="{tr}Number of posts by Anonymous users{/tr}">Anon</abbr></th>
						{/if}
						{if $gBitUser->hasPermission('p_boards_edit')}
							<th style="width:10%;" colspan="2">Actions</th>
						{/if}
					</tr>
				{/if}

				{foreach item=thread from=$threadList}
					<tr class="{cycle values="even,odd"} {if $gBitSystem->isFeatureActive('boards_post_anon_moderation') && $thread.unreg > 0}unapproved{elseif $thread.th_moved>0}moved{/if} {if $thread.th_sticky==1} highlight{/if}" >
						<td style="white-space:nowrap;">{* topic status icons *}
							{if $thread.th_moved>0}
								{biticon ipackage="icons" iname="go-jump" iexplain="Moved Topic"}
							{else}
								{assign var=flip value=$thread.flip}
								{foreach from=$flip item=flip_s key=flip_name}
									{include file="bitpackage:boards/flipswitch.tpl"}
								{/foreach}
							{/if}
						</td>

						<td>
							<a href="{$thread.url}" title="{$thread.title|escape}">{$thread.title|escape}</a>
							{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='list' serviceHash=$thread}
						</td>

						<td style="text-align:center;">{if $thread.post_count-1}{$thread.post_count-1}{/if}</td>

						<td style="text-align:center;">
							{$thread.flc_created|reltime:short|escape}<br/>
							{if $thread.flc_user_id < 0}{$thread.anon_name|escape}{else}{displayname user_id=$thread.flc_user_id}{/if}
						</td>

						<td style="text-align:center;">
							{if $thread.post_count > 1}{$thread.llc_last_modified|reltime:short|escape}{else}{/if}<br/>
							{if $thread.post_count > 1}{if $thread.llc_user_id < 0}{$thread.l_anon_name|escape}{else}{displayname user_id=$thread.llc_user_id}{/if}{else}{/if}
						</td>

						{if $gBitUser->hasPermission('p_boards_edit') || $gBitUser->hasPermission('p_boards_post_edit')}
							<td style="text-align:center;">{if $thread.unreg > 0}<a class="highlight" href="{$thread.url}" title="{$thread.title|escape}">{$thread.unreg}</a>{/if}</td>
						{/if}

						{if $gBitUser->hasPermission('p_boards_edit') || $gBitUser->hasPermission('p_boards_post_edit')}
							<td class="actionicon">
								{if $thread.flc_user_id<0 && $thread.first_approved==0}
									<a title="{tr}Approve First Post{/tr}" href="{$smarty.const.BOARDS_PKG_URL}topic.php?b={$board->mInfo.board_id}&amp;action=1&amp;comment_id={$thread.th_thread_id}">
										{biticon ipackage="icons" iname="list-add" iexplain="Approve First Post" iforce="icon"}
									</a>
									<a title="{tr}Reject First Post{/tr}" href="{$smarty.const.BOARDS_PKG_URL}topic.php?b={$board->mInfo.board_id}&amp;action=2&amp;comment_id={$thread.th_thread_id}">
										{biticon ipackage="icons" iname="list-remove" iexplain="Reject First Post" iforce="icon"}
									</a>
								{/if}

								{if $thread.th_moved==0}
									{if $gBitUser->hasPermission( 'p_boards_edit' )}
										{*smartlink ititle="Edit" ifile="edit.php" ibiticon="liberty/edit" board_id=$thread.board_id*}
										<a {if !$gBitThemes->isJavascriptEnabled()}
											href="{$smarty.const.BOARDS_PKG_URL}topic_move.php?t={$thread.th_thread_id|escape:"url"}"
											{else}
											href="javascript:void(0);" onclick="BitBoards.moveThread( 'move_block_{$thread.th_thread_id|escape:"url"}', 'move_{$thread.th_thread_id|escape:"url"}', '{$smarty.const.BOARDS_PKG_URL}ajax.php?req=1&amp;seq=' + new Date().getTime(), this );"
											{/if}
											title="{tr}Move Thread{/tr}">{biticon ipackage=icons iname="go-jump" iexplain="Move Thread" iforce="icon"}</a>
									{/if}
								{/if}

								{if $thread.th_moved==0 && $gBitUser->hasPermission( 'p_boards_remove' )}
									<a title="{tr}Delete Topic{/tr}" href="{$smarty.const.BOARDS_PKG_URL}topic.php?b={$smarty.request.b}&amp;remove=1&amp;thread_id={$thread.th_thread_id|escape:"url"}">{biticon ipackage="icons" iname="edit-delete" iexplain="Delete Thread" iforce="icon"}</a>
								{else}
									{biticon ipackage=liberty iname=spacer iforce="icon"}
								{/if}

								{if $thread.th_moved==0}
									{if $gBitUser->hasPermission( 'p_boards_edit' )}
										<br />
										<div style="display:none;" id="move_block_{$thread.th_thread_id|escape:"url"}">
											Move&nbsp;to:&nbsp;<select onchange="window.location=('{$smarty.const.BOARDS_PKG_URL}topic_move.php?t={$thread.th_thread_id|escape:"url"}&amp;target='+
												document.getElementById('move_{$thread.th_thread_id|escape:"url"}').value);" id="move_{$thread.th_thread_id|escape:"url"}">
												<option></option>
											</select>
										</div>
									{/if}
								{/if}
							</td>

							{if $thread.th_moved==0 && $gBitUser->hasPermission( 'p_boards_remove' )}
								<td>
									<input type="checkbox" name="checked[]" title="{$thread.title|escape}" value="{$thread.th_thread_id}" />
								</td>
							{/if}
						{/if}
					</tr>
				{foreachelse}
					<tr class="norecords"><td colspan="16">
						{tr}No records found{/tr}
					</td></tr>
				{/foreach}
			</table>

			{if $board->hasPostCommentsPermission()}
				<div class="navbar">
					<a class="button" title="{tr}New Topic{/tr}" href="{$comments_return_url}&amp;post_comment_request=1#editcomments">{biticon ipackage="icons" iname="mail-message-new" iexplain="New Topic" iforce="icon"} {tr}New Topic{/tr}</a>
				</div>
			{/if}

			{if $gBitUser->hasPermission( 'p_boards_remove' )}
				<div style="text-align:right;">
					<script type="text/javascript">/* <![CDATA[ check / uncheck all */
						document.write("<label for=\"switcher\">{tr}Select All{/tr}</label> ");
						document.write("<input name=\"switcher\" id=\"switcher\" type=\"checkbox\" onclick=\"switchCheckboxes(this.form.id,'checked[]','switcher')\" /><br />");
					/* ]]> */</script>

					<input type="hidden" name="b" value="{$smarty.request.b}" />

					<select name="submit_mult" onchange="this.form.submit();">
						<option value="" selected="selected">{tr}with checked{/tr}:</option>
						{if $gBitUser->hasPermission( 'p_boards_remove' )}
							<option value="remove_boards">{tr}remove{/tr}</option>
						{/if}
					</select>

					<noscript><div><input type="submit" value="{tr}Submit{/tr}" /></div></noscript>
				</div>
			{/if}
		{/form}

		{pagination b=$smarty.request.b}

		{include file="bitpackage:boards/legend_inc.tpl" topicicons=1}
	</div><!-- end .body -->
</div><!-- end .admin -->

{if $gBitSystem->isFeatureActive('boards_post_anon_moderation') && $smarty.request.post_comment_request && !$gBitUser->isRegistered()}
	{formfeedback warning="Your post will not be shown immediately it will have to be approved by a moderator"}
{/if}

{include file="bitpackage:liberty/comments_post_inc.tpl"  post_title="Post" hide=1}
{/strip}
