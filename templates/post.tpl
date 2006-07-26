{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/post.tpl,v 1.7 2006/07/26 22:45:30 hash9 Exp $ *}
{strip}
<div class="floaticon">
{assign var=flip value=$thread->getFlipFlop()}
{foreach from=$flip item=flip_s key=flip_name}
		{include file="bitpackage:bitboards/flipswitch.tpl"}
{/foreach}
{*{assign var=flip_name value="locked"}
{include file="bitpackage:bitboards/flipswitch.tpl"}
{assign var=flip_name value="sticky"}
{include file="bitpackage:bitboards/flipswitch.tpl"}*}
{if !$topic_locked}<a title="{tr}Post on this thread{/tr}" href="{$comments_return_url}&post_comment_reply_id={$thread->mInfo.flc_content_id}&post_comment_request=1#editcomments"> {biticon ipackage=bitboard iname="mail_new" iexplain="Post on this thread"}</a>{/if}

{bithelp}</div>

<div class="listing bitboard">
	<div class="header">
		<h1>{$thread->mInfo.title|escape}</h1>
		Back to <a href="{$board->mInfo.display_url}">{$board->mInfo.title|escape}</a><br>
	</div>

	<div class="body">
		{include file="bitpackage:liberty/comments_display_option_bar.tpl"}
		{minifind sort_mode=$sort_mode thread_id=$smarty.request.thread_id}
		{formfeedback hash=$formfeedback}
		<table class="mb-table">
			{foreach item=comment from=$comments}
				{cycle values="even,odd" print=false assign=cycle_var}
				<tr class="{$cycle_var} {if $gBitSystem->isFeatureActive('bitboards_post_anon_moderation') && $comment.user_id<0 and $comment.approved==0}mb-{$cycle_var}-unapproved{/if}">
				{assign var=thread_mInfo value=$thread->mInfo}
					{displaycomment comment=$comment template=$comment_template}
				</tr>
			{foreachelse}
				<tr class="norecords"><td colspan="16">
					{tr}No posts found{/tr}
				</td></tr>
			{/foreach}
			{if $post_comment_preview}
			<tr><td colspan="10">&nbsp;</td></tr>
			<tr><td colspan="10"><h2 style="text-align:center; padding:.5em">{tr}{$post_title} Preview{/tr}</h2></td></tr>
			<tr>
			<div class="preview">
				{displaycomment comment=$postComment template=$comment_template}
			</div><!-- end .preview -->
			</tr>
			<tr><td colspan="10">&nbsp;</td></tr>
	{/if}
		</table>
		{if !$topic_locked}<p style="text-align: right;"><a title="{tr}Post on this thread{/tr}" href="{$comments_return_url}&post_comment_reply_id={$thread->mInfo.flc_content_id}&post_comment_request=1#editcomments">{tr}Post on this thread{/tr} {biticon ipackage=bitboard iname="mail_new" iexplain="Post on this thread"}</a></p>{/if}

		{libertypagination ihash=$commentsPgnHash}
	</div><!-- end .body -->
</div><!-- end .admin -->
		{if $gBitSystem->isFeatureActive('bitboards_post_anon_moderation') && $smarty.request.post_comment_request && !$gBitUser->isRegistered()}
		{formfeedback warning="Your post will not be shown immediately it will have to be approved by a moderator"}
		{/if}
		{include file="bitpackage:liberty/comments_post_inc.tpl"  post_title="Post" hide=1 preview_override=1}
{/strip}

