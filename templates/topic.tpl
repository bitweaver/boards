{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/topic.tpl,v 1.6 2006/07/26 22:45:30 hash9 Exp $ *}
{strip}
<div class="listing bitboard">
	<div class="floaticon">
		{if $print_page ne 'y'}
			{if $gBitUser->hasPermission( 'p_bitforum_edit' )}
				<a title="{tr}Remove this bitforum{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}edit.php?b={$board->mInfo.board_id}">{biticon ipackage=liberty iname="edit" iexplain="Edit BitForum"}</a>
			{/if}
			{if $gBitUser->hasPermission( 'p_bitforum_remove' )}
				<a title="{tr}Remove this bitforum{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}remove.php?b={$board->mInfo.board_id}">{biticon ipackage=liberty iname="delete" iexplain="Remove BitForum"}</a>
			{/if}
		{/if}<!-- end print_page -->
	</div><!-- end .floaticon -->
	<div class="header">
		<h1>{$board->mInfo.title|escape|default:"Forum Topic"} <a id='content_1' href="{$comments_return_url}&show={if empty($smarty.request.show)}1{else}0{/if}" onclick="{literal}if (this.innerHTML=='-') { document.getElementById('content_div').style.display='none'; this.innerHTML='+'; } else { document.getElementById('content_div').style.display='block'; this.innerHTML='-'; } return false;{/literal}">{if empty($smarty.request.show)}+{else}-{/if}</a></h1>
		<div class="date">
			{tr}Created by{/tr}: {displayname user=$board->mInfo.creator_user user_id=$board->mInfo.creator_user_id real_name=$board->mInfo.creator_real_name} on {$board->getField('created')|bit_short_datetime}

			{if $board->getField('last_modified') != $board->getField('created')}
				&nbsp; {tr}Edited by{/tr}: {displayname user=$board->mInfo.modifier_user user_id=$board->mInfo.modifier_user_id real_name=$board->mInfo.modifier_real_name}, {$board->getField('last_modified')|bit_short_datetime}
			{/if}
		</div>

		Back to <a href="{$cat_url}">{$board->mInfo.content_type.content_description}s</a>
	</div>

	<div class="body">
		<div id="content_div" class="content" style="text-align: right; {if empty($smarty.request.show)}display: none;{/if}">
			{$board->mInfo.parsed_data}
		</div><!-- end .content -->
		<p style="text-align: right; margin: 0px; padding: 0px;"><a title="{tr}Start a new thread{/tr}" href="{$comments_return_url}&amp;post_comment_request=1#editcomments">{tr}Start a new thread{/tr} {biticon ipackage=bitboard iname="mail_new" iexplain="Start a new thread"}</a></p>
		{minifind sort_mode=$sort_mode board_id=$smarty.request.board_id}
		{form id="checkform"}
			<input type="hidden" name="board_id" value="{$smarty.request.board_id}" />
			<input type="hidden" name="offset" value="{$control.offset|escape}" />
			<input type="hidden" name="sort_mode" value="{$control.sort_mode|escape}" />

			<table class="mb-table">
				{foreach item=thread from=$threadList}
				{cycle values="even,odd" print=false assign=cycle_var}
				<tr class="
 					{$cycle_var} {if $thread.unreg > 0} mb-{$cycle_var}-unapproved
					{elseif $thread.th_moved>0} mb-{$cycle_var}-moved
					{/if}
					{if $thread.th_sticky==1} mb-sticky{/if}">
					<td class="actionicon">{* thread status icons *}
						{if $thread.th_moved>0}
							{biticon ipackage=bitboard iname="move" iexplain="Moved Thread"}
						{/if}
					</td>
					{assign var=flip value=$thread.flip}
					{foreach from=$flip item=flip_s key=flip_name}
						<td class="actionicon">{* thread status icons *}
							{if $thread.th_moved<=0}
								{include file="bitpackage:bitboards/flipswitch.tpl"}
							{/if}
						</td>
					{/foreach}
					<td>
						<a href="{$thread.url}" title="{$thread.title|escape}">{$thread.title|escape}</a>, started by {if $thread.flc_user_id < 0}{$thread.first_unreg_uname|escape}{else}{displayname user_id=$thread.flc_user_id}{/if} {$thread.flc_created|reltime|escape}{if $thread.post_count > 1}, with {$thread.post_count|escape} posts,
						last update by {if $thread.llc_user_id < 0}{$thread.llc_anon_name|escape}{else}{displayname user_id=$thread.llc_user_id}{/if} {$thread.llc_last_modified|reltime|escape}{/if}
					</td>
					{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
					<td style="text-align:right;">{if $thread.unreg > 0}<a style="color: blue;" href="{$thread.url}" title="{$thread.title}">{$thread.unreg}&nbsp;Unregistered&nbsp;Posts</a>{/if}</td>
						{if ($gBitUser->hasPermission( 'p_bitboards_edit' )||$gBitUser->hasPermission( 'p_bitboards_remove' ))}
							<td class="actionicon">
							{if $thread.th_moved==0}
								{if $gBitUser->hasPermission( 'p_bitboards_edit' )}
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
							{/if}
							</td>
							<td class="actionicon">
								{if $thread.th_moved==0 && $gBitUser->hasPermission( 'p_bitboards_remove' )}
									<a title="{tr}Delete Thread{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}remove_bitboard.php?thread_id={$thread.th_thread_id|escape:"url"}">{biticon ipackage=bitboard iname="mail_delete" iexplain="Delete Thread"}</a>
								{/if}
							</td>
							<td class="actionicon">
								{if $thread.th_moved==0 && $gBitUser->hasPermission( 'p_bitboards_remove' )}
									<input type="checkbox" name="checked[]" title="{$thread.title|escape}" value="{$thread.th_thread_id}" />
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
		{/form}
		{pagination b=$smarty.request.b}
	</div><!-- end .body -->
</div><!-- end .admin -->
{if $gBitSystem->isFeatureActive('bitboards_post_anon_moderation') && $smarty.request.post_comment_request && !$gBitUser->isRegistered()}
	{formfeedback warning="Your post will not be shown immediately it will have to be approved by a moderator"}
{/if}
{include file="bitpackage:liberty/comments_post_inc.tpl"  post_title="Post" hide=1}
{/strip}
