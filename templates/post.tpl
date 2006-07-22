{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/post.tpl,v 1.6 2006/07/22 15:05:14 hash9 Exp $ *}
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
		Back to <a href="{$board->mInfo.display_url}">{$board->mInfo.title|escape}</a>
	</div>

	<div class="body">
		{include file="bitpackage:liberty/comments_display_option_bar.tpl"}
		{minifind sort_mode=$sort_mode thread_id=$smarty.request.thread_id}
		{formfeedback hash=$formfeedback}
		<table class="mb-table">
			{foreach item=comment from=$comments}
				<tr class="mb-row-{cycle values="even,odd"}{if $post->mInfo.deleted==1}-deleted{else}{if $post->mInfo.user_id<0 and $post->mInfo.approved==0}-unapproved{/if}{/if}">
					{displaycomment comment=$comment template=$comment_template}
				</tr>
			{foreachelse}
				<tr class="norecords"><td colspan="16">
					{tr}No posts found{/tr}
				</td></tr>
			{/foreach}
		</table>
		{if !$topic_locked}<p style="text-align: right;"><a title="{tr}Post on this thread{/tr}" href="{$comments_return_url}&post_comment_reply_id={$thread->mInfo.flc_content_id}&post_comment_request=1#editcomments">{tr}Post on this thread{/tr} {biticon ipackage=bitboard iname="mail_new" iexplain="Post on this thread"}</a></p>{/if}

		{libertypagination ihash=$commentsPgnHash}
	</div><!-- end .body -->
</div><!-- end .admin -->
		{include file="bitpackage:liberty/comments_post_inc.tpl"  post_title="Post" hide=1}
{/strip}

