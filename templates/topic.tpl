{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/topic.tpl,v 1.2 2006/07/06 14:31:24 hash9 Exp $ *}
{strip}
<div class="listing bitboard">
	<div class="header">
		<h1>{$board->mInfo.title|escape|default:"Forum Topic"}</h1>
		<div class="date">
			{tr}Posted by{/tr}: {displayname user=$board->mInfo.creator_user user_id=$board->mInfo.creator_user_id real_name=$board->mInfo.creator_real_name} on {$board->getField('created')|bit_short_datetime}

			{if $board->getField('last_modified') != $board->getField('created')}
				<br/>{tr}Edited by{/tr}: {displayname user=$board->mInfo.modifier_user user_id=$board->mInfo.modifier_user_id real_name=$board->mInfo.modifier_real_name}, {$board->getField('last_modified')|bit_short_datetime}
			{/if}
		</div>
		
		<span style="text-align: right; width: 100%;">Back to <a href="{$cat_url}">{$board->mInfo.content_type.content_description}s</a></span>
	</div>

	<div class="body">
		<p style="text-align: right; margin: 0px; padding: 0px;"><a title="{tr}Start a new thread{/tr}" href="{$comments_return_url}&amp;post_comment_request=1#editcomments">{tr}Start a new thread{/tr} {biticon ipackage=bitboard iname="mail_new" iexplain="Start a new thread"}</a></p>
		{minifind sort_mode=$sort_mode board_id=$smarty.request.board_id}
		{form id="checkform"}
			<input type="hidden" name="board_id" value="{$smarty.request.board_id}" />
			<input type="hidden" name="offset" value="{$control.offset|escape}" />
			<input type="hidden" name="sort_mode" value="{$control.sort_mode|escape}" />

			<table class="mb-table">
			{*<tr>
					<th>{smartlink ititle="Title" isort=flc_title iurl=$request.url offset=$control.offset}</th>
					<th>{smartlink ititle="Started By" isort=flc_user_id offset=$control.offset}</th>
					<th>{smartlink ititle="Started" isort=flc_created offset=$control.offset}</th>
					<th>{smartlink ititle="Last Update By" isort=llc_user_id offset=$control.offset}</th>
					<th>{smartlink ititle="Last Update" isort=llc_created offset=$control.offset}</th>
					{if $gBitUser->hasPermission( 'p_bitboard_remove' )}
						<th>{tr}Actions{/tr}</th>
					{/if}
				</tr>*}

				{foreach item=thread from=$threadList}
					<tr class="mb-row-{cycle values="even,odd"}
					{if $thread.first_deleted==1 or $thread.th_deleted==1}
						-deleted
					{else}
						{if $thread.th_moved>0}
							-moved
						{else}
							{if $thread.unreg > 0 or $thread.flc_user_id<0 and $thread.first_approved==0}
								-unapproved
							{/if}
						{/if}
					{/if}
					{if $thread.th_sticky==1} sticky{/if}"
					{if $thread.th_sticky} style="background-color: red;"{/if}>
					<td class="actionicon">{* thread status icons *}
						{if $thread.th_moved>0}
							{biticon ipackage=bitboard iname="move" iexplain="Moved Thread"}
						{else}
							{if $thread.first_deleted==1 or $thread.th_deleted==1}
								{biticon ipackage=bitboard iname="deleted" iexplain="Deleted Thread"}
							{/if}
							{assign var=flip value=$thread.flip}
							{assign var=flip_name value="locked"}
							{include file="bitpackage:bitboards/flipswitch.tpl"}
							{assign var=flip_name value="sticky"}
							{include file="bitpackage:bitboards/flipswitch.tpl"}
						{/if}
					</td>
					<td>
						<a href="{$thread.url}" title="{$thread.flc_title}">{$thread.flc_title|escape}</a>, started by {if $thread.flc_user_id < 0}{$thread.first_unreg_uname|escape}{else}{displayname user_id=$thread.flc_user_id}{/if} {$thread.flc_created|reltime|escape}{if $thread.post_count > 1}, with {$thread.post_count|escape} posts, last update by {if $thread.flc_user_id < 0}{$thread.first_unreg_uname|escape}{else}{displayname user_id=$thread.flc_user_id}{/if} {$thread.llc_last_modified|reltime|escape}{/if}.
					</td>
					{if $gBitUser->hasPermission('p_bitboard_edit') || $gBitUser->hasPermission('p_bitforum_post_edit')}
					<td style="text-align:right;">{if $thread.unreg > 0}<a style="color: blue;" href="{$smarty.const.BITBOARDS_PKG_URL}index.php?board_id={$thread.th_board_id|escape:"url"}&thread_id={$thread.th_thread_id|escape:"url"}" title="{$thread.flc_title}">{$thread.unreg}&nbsp;Unregistered&nbsp;Posts</a>{/if}</td>
						{if ($gBitUser->hasPermission( 'p_bitboard_edit' )||$gBitUser->hasPermission( 'p_bitforum_remove' ))}
							<td class="actionicon">
							{if $thread.th_moved==0}
								{if $gBitUser->hasPermission( 'p_bitboard_edit' )}
									{*smartlink ititle="Edit" ifile="edit.php" ibiticon="liberty/edit" board_id=$thread.board_id*}
									<a onclick="
									document.getElementById('move_block_{$thread.th_thread_id|escape:"url"}').style['display']='inline'; 
									var url = '{$smarty.const.BITBOARDS_PKG_URL}ajax.php?req=1&seq=' + new Date().getTime();
									var element = 'move_{$thread.th_thread_id|escape:"url"}';
									var params = null;
									{literal}
									var ajax = new Ajax.Updater(
										{success: element},
										url, {method: 'get', parameters: params, onFailure: reportError}
									);
									{/literal}
									this.oldonclick=this.onclick;
									this.onclick=new Function('
										document.getElementById(\'move_block_{$thread.th_thread_id|escape:"url"}\').style[\'display\']=\'none\'; 
										document.getElementById(\'move_{$thread.th_thread_id|escape:"url"}\').innerHTML=\'\';
										this.onclick=this.oldonclick;
										return false;
									');
									return false;" title="{tr}Move Thread{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}topic_move.php?t={$thread.th_thread_id|escape:"url"}">{biticon ipackage=bitboard iname="mail_forward" iexplain="Move Thread"}</a>
									<div style="display:none;" id="move_block_{$thread.th_thread_id|escape:"url"}">
										Move to: <select onchange="window.location=('{$smarty.const.BITBOARDS_PKG_URL}topic_move.php?t={$thread.th_thread_id|escape:"url"}&target='+
												document.getElementById('move_{$thread.th_thread_id|escape:"url"}').value);" id="move_{$thread.th_thread_id|escape:"url"}">
											</select>
									</div>
								{/if}
								{if $gBitUser->hasPermission( 'p_bitboard_remove' )}
									<a title="{tr}Delete Thread{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}remove_bitboard.php?thread_id={$thread.th_thread_id|escape:"url"}">{biticon ipackage=bitboard iname="mail_delete" iexplain="Delete Thread"}</a>
									<input type="checkbox" name="checked[]" title="{$thread.flc_title|escape}" value="{$thread.th_thread_id}" />
								{/if}
							{/if}
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

			<p style="text-align: right;"><a title="{tr}Start a new thread{/tr}" href="{$comments_return_url}&amp;post_comment_request=1#editcomments">{tr}Start a new thread{/tr} {biticon ipackage=bitboard iname="mail_new" iexplain="Start a new thread"}</a></p>
			{if $gBitUser->hasPermission( 'p_bitboard_remove' )}
				<div style="text-align:right;">
					<script type="text/javascript">/* <![CDATA[ check / uncheck all */
					document.write("<label for=\"switcher\">{tr}Select All{/tr}</label> ");
					document.write("<input name=\"switcher\" id=\"switcher\" type=\"checkbox\" onclick=\"switchCheckboxes(this.form.id,'checked[]','switcher')\" /><br />");
					/* ]]> */</script>

					<select name="submit_mult" onchange="this.form.submit();">
						<option value="" selected="selected">{tr}with checked{/tr}:</option>
						{if $gBitUser->hasPermission( 'p_bitboard_remove' )}
							<option value="remove_bitboards">{tr}remove{/tr}</option>
						{/if}
					</select>

					<noscript><div><input type="submit" value="{tr}Submit{/tr}" /></div></noscript>
				</div>
			{/if}
		{/form}
		{pagination}
	</div><!-- end .body -->
</div><!-- end .admin -->
{include file="bitpackage:bitboards/comment_post.tpl"}
{/strip}
