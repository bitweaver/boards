{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/post.tpl,v 1.13 2006/09/01 12:32:23 squareing Exp $ *}
{strip}
<div class="floaticon">
	{assign var=flip value=$thread->getFlipFlop()}
	{foreach from=$flip item=flip_s key=flip_name}
		{include file="bitpackage:bitboards/flipswitch.tpl"}
	{/foreach}
	{if !$topic_locked}<a title="{tr}Post Reply{/tr}" href="{$comments_return_url}&amp;post_comment_reply_id={$thread->mInfo.flc_content_id}&amp;post_comment_request=1#editcomments"> {biticon ipackage=bitboard iname="post_reply" iexplain="Post Reply"}</a>{/if}
</div>

<div class="listing bitboard">
	<div class="header">
		<h1>{$thread->mInfo.title|escape}</h1>
		Back to <a href="{$board->mInfo.display_url}">{$board->mInfo.title|escape}</a>
	</div>

	<div class="body">
		{if !$topic_locked}
			<div class="navbar">
				<a title="{tr}New Topic{/tr}" href="{$comments_return_url}&amp;post_comment_reply_id={$thread->mInfo.flc_content_id}&amp;post_comment_request=1#editcomments">{tr}Post Reply{/tr} {biticon ipackage=bitboard iname="new_topic" iexplain="New Topic"}</a>
			</div>
		{/if}

		{* not happy with this yet - xing *}
		{form action="$comments_return_url" class="mb-threading"}
			<input type="hidden" name="t" value="{$smarty.request.t}" />
			<input type="hidden" name="comment_page" value="{$smarty.request.comment_page}" />
			<label>
				{tr}Threaded{/tr}: <input type="checkbox" name="comments_style" id="comments-style" value="threaded" {if $comments_style eq "threaded"}checked="checked"{/if} onchange="this.parentNode.submit()"/>
			</label>
			&nbsp; &nbsp;
			<label>
				{tr}Messages{/tr}: <select name="comments_maxComments" id="comments-maxcomm" onchange="this.parentNode.submit()">
					<option value="5" {if $maxComments eq 5}selected="selected"{/if}>5</option>
					<option value="10" {if $maxComments eq 10}selected="selected"{/if}>10</option>
					<option value="20" {if $maxComments eq 20}selected="selected"{/if}>20</option>
					<option value="50" {if $maxComments eq 50}selected="selected"{/if}>50</option>
					<option value="100" {if $maxComments eq 100}selected="selected"{/if}>100</option>
					<option value="999999" {if $maxComments eq 999999}selected="selected"{/if}>All</option>
				</select> <input type="submit" name="comments_setOptions" value="set" id="set_btn"/>
			</label>
			<script>/*<![CDATA[*/
				document.getElementById('set_btn').parentNode.removeChild(document.getElementById('set_btn'));
			/*]]>*/</script>
		{/form}

		{formfeedback hash=$formfeedback}

		{foreach item=comment from=$comments}
			<div class="mb-post {cycle values="even,odd"}{if $gBitSystem->isFeatureActive('bitboards_post_anon_moderation') && $comment.user_id<0 and $comment.approved==0} unapproved{/if}">
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

		{if !$topic_locked}
			<div class="navbar">
				<a title="{tr}New Topic{/tr}" href="{$comments_return_url}&amp;post_comment_reply_id={$thread->mInfo.flc_content_id}&amp;post_comment_request=1#editcomments">{tr}Post Reply{/tr} {biticon ipackage=bitboard iname="new_topic" iexplain="New Topic"}</a>
			</div>
		{/if}

		{libertypagination ihash=$commentsPgnHash}
	</div><!-- end .body -->
</div><!-- end .bitboard -->

{if $gBitSystem->isFeatureActive('bitboards_post_anon_moderation') && ($smarty.request.post_comment_request || !empty($smarty.request.post_comment_submit)) && !$gBitUser->isRegistered()}
	{formfeedback warning="Your post will not be shown immediately it will have to be approved by a moderator"}
{/if}

{include file="bitpackage:liberty/comments_post_inc.tpl"  post_title="Post" hide=1 preview_override=1}

{include file="bitpackage:bitboards/legend_inc.tpl"  posticons=1}
{/strip}

