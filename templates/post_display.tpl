{strip}
{assign var='gContent' value=$comment}

<div class="body" id="{$comment.comment_id|escape}" {if $comments_style eq 'threaded'}style="padding-left:{math equation="level * marginIncrement +3 " level=$comment.level marginIncrement=20}px"{/if}>
	<div class="content">
		{if !$post_is_preview}
			<div class="floaticon">
				{if $print_page ne 'y' && $comment.deleted==0 }
					{if !$topic_locked && $gBitUser->hasPermission( 'p_liberty_post_comments' )}
						<a href="{$comments_return_url}&amp;post_comment_reply_id={$comment.content_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="icons" iname="mail-reply-sender" iexplain="Reply to this Post"}</a>
					{/if}
					{if !$topic_locked && $gBitUser->hasPermission( 'p_liberty_post_comments' )}
						<a href="{$comments_return_url}&amp;post_comment_reply_id={$comment.content_id}&amp;post_comment_request=1&amp;quote=y#editcomments" rel="nofollow">{biticon ipackage="icons" iname="mail-reply-all" iexplain="Reply with Quote to this Post"}</a>
					{/if}
					{if $comment.editable}
						<a href="{$comments_return_url}&amp;post_comment_id={$comment.comment_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit"}</a>
					{/if}
					{if $gBitUser->isAdmin()}
						<a href="{$comments_return_url}&amp;delete_comment_id={$comment.comment_id}" rel="nofollow">{biticon ipackage="icons" iname="edit-delete" iexplain="Remove"}</a>
					{/if}
					{if $gBitUser->hasPermission( 'p_bitboards_edit' ) && (($comment.user_id<0 && $comment.approved==0)||$comment.user_id>=0) && !$comment.warned}
						{if $comment.user_id<0 && $comment.approved==0}
							<a title="{tr}Approve this post{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}post.php?t={$thread->mRootId}&amp;action=1&amp;comment_id={$comment.comment_id}">
								{biticon ipackage="icons" iname="list-add" iexplain="Approve Post"}
							</a>

							<a title="{tr}Reject this post{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}post.php?t={$thread->mRootId}&amp;action=2&amp;comment_id={$comment.comment_id}">
								{biticon ipackage="icons" iname="list-remove" iexplain="Reject Post"}
							</a>
						{elseif !$comment.warned && $comment.user_id>=0}
							<a onclick="
									this.oldonclick=this.onclick;
									document.getElementById('warn_block_{$comment.comment_id|escape:"url"}').style['display']='inline';
									this.onclick=new Function('
										document.getElementById(\'warn_block_{$comment.comment_id|escape:"url"}\').style[\'display\']=\'none\';
										this.onclick=this.oldonclick;
										return false;
									');
									return false;
								" title="{tr}Warn the poster about this post{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}post.php?t={$thread->mRootId}&amp;action=3&amp;comment_id={$comment.comment_id}"
							>{biticon ipackage="icons" iname="dialog-warning" iexplain="Warn Post"}</a>

							<div style="display:none;" id="warn_block_{$comment.comment_id|escape:"url"}">
								{form action="`$thread_mInfo.display_url`"}
									<input type="hidden" value="3" name="action" />
									<input type="hidden" value="{$thread_mInfo.th_thread_id}" name="t" />
									<input type="hidden" value="{$comment.comment_id}" name="comment_id" />
									<textarea style="vertical-align: top;" rows="3" cols="10" name="warning_message" onclick="this.value=''; this.innerHTML=''; this.onclick=null;">
										Enter Warning Message
									</textarea>
									<input type="submit" value="Warn" />
								{/form}
							</div>
						{/if}
					{/if}
				{/if}<!-- end print_page -->
			</div><!-- end .floaticon -->
		{/if}

		<div class="header">
			{if $comment.title neq ""}<h2>{$comment.title|escape}</h2>{/if}
			<span class="date">
				{if $gBitUser->getPreference('boards_show_avatars','y')==n}
					{tr}Posted by{/tr}: {if $comment.user_id < 0}{$comment.unreg_uname|escape}{else}{displayname hash=$comment}{/if}, {else}
					{tr}Posted{/tr}: {/if}
						{$comment.created|reltime}, {if $comment.created != $comment.last_modified}
					{tr}Last modification by{/tr}:
					{if $comment.user_id < 0}
						{$comment.unreg_uname|escape}
					{else}
						{displayname user=$comment.modifier_user user_id=$comment.modifier_user_id real_name=$comment.modifier_real_name}
					{/if}, {$comment.last_modified|reltime}
				{/if}
			</span>
		</div><!-- end .header -->

		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$comment}

		{if $gBitUser->isRegistered() and $comment.warned}
			<div class="warning">
				{assign var=comment_id value=$comment.comment_id}
				<a onclick="
					var e = document.getElementById('warned_message_{$comment.comment_id|escape:"url"}');
					var url = '{$smarty.const.BITBOARDS_PKG_URL}ajax.php?req=10&comment_id={$comment_id}&seq=' + new Date().getTime();
					var element = 'warned_message_{$comment.comment_id|escape:"url"}';
					var params = null;
					{literal}
						var ajax = new Ajax.Updater(
						{success: element},
						url, {method: 'get', parameters: params, onFailure: reportError}
						);
					{/literal}
					e.style.display='block';
					this.oldonclick=this.onclick;
					this.onclick=new Function('
							document.getElementById(\'warned_message_{$comment.comment_id|escape:"url"}\').style.display=\'none\';
							this.onclick=this.oldonclick;
							return false;
						');
					return false;
					" href="{$thread_mInfo.display_url}&amp;warning[{$comment_id}]={if empty($warnings.$comment_id)}show{else}hide{/if}"
				>{biticon ipackage="icons" iname="dialog-error" iexplain="Warned Post"}</a>

				<div id="warned_message_{$comment.comment_id|escape:"url"}">
					{if !empty($warnings.$comment_id)}{$comment.warned_message}{/if}
				</div>

				{if empty($warnings.$comment_id)}
					<script>/*<![CDATA[*/
						var warned_message_{$comment.comment_id|escape:"url"} = document.getElementById('warned_message_{$comment.comment_id|escape:"url"}');
						warned_message_{$comment.comment_id|escape:"url"}.style.display='none';
					/*]]>*/</script>
				{/if}
			</div>
		{/if}

		{$comment.parsed_data}
	</div><!-- end .content -->
</div>

<div class="userinfo">
	{if $gBitUser->getPreference('boards_show_avatars','y') == y}
		<strong>{if $comment.user_id < 0}{$comment.anon_name|escape}{else}{displayname hash=$comment}{/if}</strong>
		<br />
		{if $comment.user_id >= 0 && !empty($comment.user_avatar_url)}
			<a href="{$comment.user_url}"><img src="{$comment.user_avatar_url}" title="{tr}Avatar{/tr}" alt="{tr}Avatar{/tr}" /></a>
			<br />
			<small>{tr}Joined: {/tr}{$comment.registration_date|bit_short_date}</small><br />
		{else}
			<small>{tr}Anonymous User{/tr}</small><br />
		{/if}
	{/if}
</div>

<div class="signature"> </div>
{/strip}
