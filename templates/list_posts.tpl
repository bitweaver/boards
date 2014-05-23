{* $Header$ *}
{strip}
<div class="listing boards">

	<div class="navbar">
		<div class="boards breadcrumb">
			<a href="{$smarty.const.BOARDS_PKG_URL}">{tr}Message Boards{/tr}</a> &raquo; <a href="{$board->mInfo.display_url}">{$board->getTitle()|escape}</a>
		</div>
	</div>

	<div class="floaticon floatctrl">
		{* not happy with this yet - xing *}
		{form action="$comments_return_url" class="form-inline mb-threading"}
			<input type="hidden" name="t" value="{$smarty.request.t}" />
			{* always go back to page 1 since changing any of these values
			   repaginates and makes the current page number meaningless *}
			<input type="hidden" name="comment_page" value="1" />
			<i class="icon-sort"></i> <select name="comments_sort_mode" id="comments-sort">
				<option value="commentDate_desc" {if $comments_sort_mode eq "commentDate_desc"}selected="selected"{/if}>{tr}Newest{/tr}</option>
				<option value="commentDate_asc" {if $comments_sort_mode eq "commentDate_asc"}selected="selected"{/if}>{tr}Oldest{/tr}</option>
			</select>
			&nbsp;&nbsp;
			<label>
				<input type="checkbox" name="comments_style" id="comments-style" value="threaded" {if $comments_style eq "threaded"}checked="checked"{/if} onchange="this.form.submit()"/> {tr}Threaded{/tr}
			</label>
			&nbsp; &nbsp;
			<label>
				<select name="comments_maxComments" id="comments-maxcomm" onchange="this.form.submit()">
					<option value="5" {if $maxComments eq 5}selected="selected"{/if}>5</option>
					<option value="10" {if $maxComments eq 10}selected="selected"{/if}>10</option>
					<option value="20" {if $maxComments eq 20}selected="selected"{/if}>20</option>
					<option value="50" {if $maxComments eq 50}selected="selected"{/if}>50</option>
					<option value="100" {if $maxComments eq 100}selected="selected"{/if}>100</option>
					<option value="999999" {if $maxComments eq 999999}selected="selected"{/if}>All</option>
				</select> {tr}Messages{/tr} 
			</label>
		{/form}
		{if $gBitSystem->isPackageActive( 'rss' )}
			<a title="{tr}Get RSS Feed{/tr}" href="{$smarty.const.BOARDS_PKG_URL}rss.php?t={$smarty.request.t}">{booticon iname="icon-rss" ipackage=rss iexplain="Get RSS Feed"}</a>
		{/if}
		{assign var=flip value=$thread->getFlipFlop()}
		{foreach from=$flip item=flip_s key=flip_name}
			{include file="bitpackage:boards/flipswitch.tpl"}
		{/foreach}
	</div>

	<div class="header">
		<h1>{$thread->getTitle()|escape}</h1>
		{if $thread->getField('root_content_type_guid') != $smarty.const.BITBOARD_CONTENT_TYPE_GUID}
			{tr}Comment on{/tr} <a href="{$smarty.const.BIT_ROOT_URL}index.php?content_id={$thread->getField('root_content_id')}">{$thread->getField('root_title')|default:$thread->getField('root_content_id')|escape}</a>
		{/if}
	</div>

	<div class="body">
		{if !$topic_locked && $board->hasPostCommentsPermission()}
			<div class="control-group submit">
				<a title="{tr}Post Reply{/tr}" class="btn btn-default" href="{$comments_return_url}&amp;post_comment_reply_id={$thread->mInfo.flc_content_id}&amp;post_comment_request=1#editcomments">{booticon iname="icon-comment-alt"  ipackage="icons"  iexplain="Post Reply" iforce="icon"} {tr}Post Reply{/tr}</a>
			</div>
		{/if}

		{formfeedback hash=$formfeedback}

		{foreach item=comment from=$comments}
			<div class="mb-post {if $gBitSystem->isFeatureActive('boards_post_anon_moderation') && $comment.user_id<0 and $comment.is_approved==0} unapproved{/if}">
				{assign var=thread_mInfo value=$thread->mInfo}
				{displaycomment comment=$comment template=$comment_template}
			</div>
		{foreachelse}
			<p class="norecords">
				{tr}No posts found{/tr}
			</p>
		{/foreach}

		{if $post_comment_preview}
			<h2>{tr}Preview{/tr}</h2>
			<div class="preview mb-post">
				{assign var=post_is_preview value=1}
				{displaycomment comment=$postComment template=$comment_template}
				{assign var=post_is_preview value=0}
			</div><!-- end .preview -->
		{/if}

		{if !$topic_locked && $board->hasPostCommentsPermission()}
			<div class="control-group submit">
				<a title="{tr}Post Reply{/tr}" class="btn btn-default" href="{$comments_return_url}&amp;post_comment_reply_id={$thread->mInfo.flc_content_id}&amp;post_comment_request=1#editcomments">{booticon iname="icon-comment-alt"  ipackage="icons"  iexplain="Post Reply" iforce="icon"} {tr}Post Reply{/tr}</a>
			</div>
		{/if}

		{libertypagination ihash=$commentsPgnHash}
	</div><!-- end .body -->
</div><!-- end .boards -->

{if $gBitSystem->isFeatureActive('boards_post_anon_moderation') && ($smarty.request.post_comment_request || !empty($smarty.request.post_comment_submit)) && !$gBitUser->isRegistered()}
	{formfeedback warning="Your post will not be shown immediately it will have to be approved by a moderator"}
{/if}

{include file="bitpackage:liberty/comments_post_inc.tpl"  post_title="Post" hide=1 preview_override=1}

{include file="bitpackage:boards/legend_inc.tpl"  posticons=1}
{/strip}
