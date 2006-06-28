{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/post.tpl,v 1.1 2006/06/28 15:45:26 spiderr Exp $ *}
{strip}
		{if $comments and $gBitSystem->isFeatureActive('comments_display_option_bar')}
			{form action="`$comments_return_url`#editcomments"}
				<input type="hidden" name="post_comment_reply_id" value="{$post_comment_reply_id}" />
				<input type="hidden" name="post_comment_id" value="{$post_comment_id}" />
				<table class="optionbar">
					<caption>{tr}Comments Filter{/tr}</caption>
					<tr>
						<td>
							<label for="comments-maxcomm">{tr}Messages{/tr} </label>
							<select name="comments_maxComments" id="comments-maxcomm">
								{* 1 comment selection is used for directly displaying a single comment via a URL *}
								<option value="1" {if $maxComments eq 1}selected="selected"{/if}>1</option>
								<option value="5" {if $maxComments eq 5}selected="selected"{/if}>5</option>
								<option value="10" {if $maxComments eq 10}selected="selected"{/if}>10</option>
								<option value="20" {if $maxComments eq 20}selected="selected"{/if}>20</option>
								<option value="50" {if $maxComments eq 50}selected="selected"{/if}>50</option>
								<option value="100" {if $maxComments eq 100}selected="selected"{/if}>100</option>
								<option value="999999" {if $maxComments eq 999999}selected="selected"{/if}>All</option>
							</select>
						</td>
						<td>
							<label for="comments-style">{tr}Style{/tr} </label>
							<select name="comments_style" id="comments-style">
								<option value="flat" {if $comments_style eq "flat"}selected="selected"{/if}>Flat</option>
								<option value="threaded" {if $comments_style eq "threaded"}selected="selected"{/if}>Threaded</option>
							</select>
						</td>
						<td>
							<label for="comments-sort">{tr}Sort{/tr} </label>
							<select name="comments_sort_mode" id="comments-sort">
								<option value="commentDate_desc" {if $comments_sort_mode eq "commentDate_desc"}selected="selected"{/if}>Newest first</option>
								<option value="commentDate_asc" {if $comments_sort_mode eq "commentDate_asc"}selected="selected"{/if}>Oldest first</option>
							</select>
						</td>
						<td style="text-align:right"><input type="submit" name="comments_setOptions" value="set" /></td>
					</tr>
				</table>
			{/form}
		{/if}
<div class="floaticon">
{assign var=flip value=$thread->getFlipFlop()}
{assign var=flip_name value="locked"}
{include file="bitpackage:bitboard/flipswitch.tpl"}
{assign var=flip_name value="sticky"}
{include file="bitpackage:bitboard/flipswitch.tpl"}
<a title="{tr}Post on this thread{/tr}" href="{$comments_return_url}&amp;post_comment_request=1#editcomments"> {biticon ipackage=bitboard iname="mail_new" iexplain="Post on this thread"}</a>
{bithelp}</div>

<div class="listing bitboard">
	<div class="header">
		<h1>{$thread->mInfo.flc_title|escape}</h1>
		<span style="text-align: right; width: 100%;">Back to <a href="{$board->mInfo.display_url}">{$board->mInfo.title|escape}</a></span>
	</div>

	<div class="body">
		{minifind sort_mode=$sort_mode thread_id=$smarty.request.thread_id}
		{form id="checkform"}
			<input type="hidden" name="thread_id" value="{$smarty.request.thread_id}" />
			<input type="hidden" name="offset" value="{$control.offset|escape}" />
			<input type="hidden" name="sort_mode" value="{$control.sort_mode|escape}" />

			<table class="mb-table">
				{foreach item=comment from=$comments}
					<tr class="mb-row-{cycle values="even,odd"}{if $post->mInfo.deleted==1}-deleted{else}{if $post->mInfo.user_id<0 and $post->mInfo.approved==0}-unapproved{/if}{/if}">
						{displaycomment comment=$comment template=$comment_template}	
					</tr>
				{foreachelse}
					<tr class="norecords"><td colspan="16">
						{tr}No records found{/tr}
					</td></tr>
				{/foreach}
			</table>
			<p style="text-align: right;"><a title="{tr}Post on this thread{/tr}" href="{$comments_return_url}&amp;post_comment_request=1#editcomments">{tr}Post on this thread{/tr} {biticon ipackage=bitboard iname="mail_new" iexplain="Post on this thread"}</a></p>
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
		{include file="bitpackage:bitboard/comment_post.tpl"}
{/strip}

