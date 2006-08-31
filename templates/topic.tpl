{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/topic.tpl,v 1.10 2006/08/31 08:07:15 spiderr Exp $ *}
{strip}
<div class="listing bitboard">
	<div class="floaticon">
		{if $print_page ne 'y'}
			{if $gBitUser->hasPermission( 'p_bitboard_edit' )}
				<a title="{tr}Remove this message board{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}edit.php?b={$board->mInfo.board_id}">{biticon ipackage=liberty iname="edit" iexplain="Edit Message Board"}</a>
			{/if}
			{if $gBitUser->hasPermission( 'p_bitboard_remove' )}
				<a title="{tr}Remove this message board{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}remove.php?b={$board->mInfo.board_id}">{biticon ipackage=liberty iname="delete" iexplain="Remove Message Board"}</a>
			{/if}
		{/if}<!-- end print_page -->
	</div><!-- end .floaticon -->
	<div class="header">
		<h1>{$board->mInfo.title|escape|default:"Message Board Topic"} <a id='content_1' href="{$comments_return_url}&show={if empty($smarty.request.show)}1{else}0{/if}" onclick="{literal}if (this.innerHTML=='-') { document.getElementById('content_div').style.display='none'; this.innerHTML='+'; } else { document.getElementById('content_div').style.display='block'; this.innerHTML='-'; } return false;{/literal}">{if empty($smarty.request.show)}+{else}-{/if}</a></h1>
		<div class="date">
			<div id="content_div" class="content" style="text-align: right; {if empty($smarty.request.show)}display: none;{/if}">
				{$board->mInfo.parsed_data}
			</div><!-- end .content -->
		</div>

		Back to <a href="{$cat_url}">{$board->mInfo.content_type.content_description}s</a>
	</div>

	<div class="body">
		<p style="text-align: right; margin: 0px; padding: 0px;"><a title="{tr}New Topic{/tr}" href="{$comments_return_url}&amp;post_comment_request=1#editcomments">{tr}New Topic{/tr} {biticon ipackage=bitboard iname="mail_new" iexplain="New Topic"}</a></p>
		{minifind sort_mode=$sort_mode b=$smarty.request.b}
		{form id="checkform"}
			<input type="hidden" name="board_id" value="{$smarty.request.board_id}" />
			<input type="hidden" name="offset" value="{$control.offset|escape}" />
			<input type="hidden" name="sort_mode" value="{$control.sort_mode|escape}" />

			<table class="mb-table">
				{if ! $gBitSystem->isFeatureActive('bitboards_thread_verbrose')}
					<th width="1" colspan="{$threadList.0.flip|@count}"><small>{if $gBitUser->isRegistered()}U{/if}TSI</small></th>
					<th style="text-align:left;white-space: nowrap;">Title</th>
					<th width="1" style="text-align: center;">Replies</th>
					<th>{tr}Started{/tr}</th>
					<th>{tr}Last Post{/tr}</th>
					{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
						<th style="text-align: center;">Anon</th>
					{/if}
					{if $gBitUser->hasPermission('p_bitboards_edit')}
						<th colspan="4" style="text-align: center;">Actions</th>
					{/if}
				{/if}
				{foreach item=thread from=$threadList}
				{cycle values="even,odd" print=false assign=cycle_var}
				<tr class="
 					{$cycle_var} {if $gBitSystem->isFeatureActive('bitboards_post_anon_moderation') &&  $thread.unreg > 0} mb-{$cycle_var}-unapproved
					{elseif $thread.th_moved>0} mb-{$cycle_var}-moved
					{/if}
					{if $thread.th_sticky==1} mb-sticky{/if}">
					{if $thread.th_moved>0}
					<td class="actionicon" width="1" colspan="{$thread.flip|@count}">{* topic status icons *}
							{biticon ipackage=bitboard iname="move" iexplain="Moved Topic"}
					</td>
					{else}
					{assign var=flip value=$thread.flip}
					{foreach from=$flip item=flip_s key=flip_name}
						<td class="actionicon" width="1">{* topic status icons *}
								{include file="bitpackage:bitboards/flipswitch.tpl"}
						</td>
					{/foreach}
					{/if}
					<td style="white-space: nowrap;"><a href="{$thread.url}" title="{$thread.title|escape}">{$thread.title|escape}</a></td>
					<td width="1" style="text-align: center;">{if $thread.post_count-1}{$thread.post_count-1|escape}{/if}</td>
					<td>
						{$thread.flc_created|reltime:short|escape}<br/>
						{if $thread.flc_user_id < 0}{$thread.anon_name|escape}{else}{displayname user_id=$thread.flc_user_id}{/if}	
					</td>
					<td style="text-align: right;">
						{if $thread.post_count > 1}{$thread.llc_last_modified|reltime:short|escape}{else}{/if}<br/>
						{if $thread.post_count > 1}{if $thread.llc_user_id < 0}{$thread.l_anon_name|escape}{else}{displayname user_id=$thread.llc_user_id}{/if}{else}{/if}
					</td>
					{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
						<td style="text-align:center;">{if $thread.unreg > 0}<a style="color: blue;" href="{$thread.url}" title="{$thread.title}">{$thread.unreg}</a>{/if}</td>
					{/if}
						{if $gBitUser->hasPermission('p_bitboards_edit') || $gBitUser->hasPermission('p_bitboards_post_edit')}
							<td class="actionicon">
								{if $thread.flc_user_id<0 && $thread.first_approved==0}
									<a title="{tr}Approve First Post{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}topic.php?b={$board->mInfo.board_id}&action=1&comment_id={$thread.th_thread_id}">
										{biticon ipackage=bitboard iname="edit_add" iexplain="Approve First Post"}
									</a>
									<a title="{tr}Reject First Post{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}topic.php?b={$board->mInfo.board_id}&action=2&comment_id={$thread.th_thread_id}">
										{biticon ipackage=bitboard iname="edit_remove" iexplain="Reject First Post"}
									</a>
								{/if}
							</td>
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
					</tr>
				{foreachelse}
					<tr class="norecords"><td colspan="16">
						{tr}No records found{/tr}
					</td></tr>
				{/foreach}
			</table>

			<p style="text-align: right;"><a title="{tr}New Topic{/tr}" href="{$comments_return_url}&amp;post_comment_request=1#editcomments">{tr}New Topic{/tr} {biticon ipackage=bitboard iname="mail_new" iexplain="New Topic"}</a></p>
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
