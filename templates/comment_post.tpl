{if $smarty.request.post_comment_request}
{strip}
<br />
<div class="edit comment">
	<div class="body"{if ( $smarty.request.post_comment_request || $post_comment_preview )} id="editcomments"{/if}>
		{formfeedback hash=$formfeedback}

		{if $post_comment_preview}
			<h2>{tr}Comments Preview{/tr}</h2>
			<div class="preview">
				{include file='bitpackage:liberty/display_comment.tpl' comment=$postComment}
			</div><!-- end .preview -->
		{/if}

		{form action="`$comments_return_url`#editcomments"}
			<input type="hidden" name="comments_maxComments" value="{$maxComments}" />
			<input type="hidden" name="comments_style" value="{$comments_style}" />
			<input type="hidden" name="comments_sort_mode" value="{$comments_sort_mode}" />

			{if $smarty.request.post_comment_request || $smarty.request.post_comment_preview}
				{legend legend="Post"}
					<input type="hidden" name="post_comment_reply_id" value="{$post_comment_reply_id}" />
				    <input type="hidden" name="post_comment_id" value="{$post_comment_id}" />

					<div class="control-group">
						{formlabel label="Title" for="comments-title"}
						{forminput}
							<input type="text" size="50" name="comment_title" id="comments-title" value="{$postComment.title|escape:html}" />
							{formhelp note=""}
						{/forminput}
					</div>

					{capture assign="textarea_help"}
						{tr}Use [http://www.foo.com] or [http://www.foo.com|description] for links.<br />HTML tags are not allowed inside comments.{/tr}
					{/capture}
					{textarea noformat=1 label="Comment" id="commentpost" name="comment_data" rows="6"}{$postComment.data}{/textarea}

					<div class="control-group submit">
						<input type="submit" name="post_comment_preview" value="{tr}Preview{/tr}"/>&nbsp;
						<input type="submit" name="post_comment_submit" value="{tr}Post{/tr}"/>&nbsp;
						<input type="submit" name="post_comment_cancel" value="{tr}Cancel{/tr}"/>
					</div>
				{/legend}
			{/if}
		{/form}

		{pagination}
	</div><!-- end .body -->
</div><!-- end .comment -->
{/strip}
{/if}
