{* $Header$ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin boards">
	<div class="header">
		<h1>{tr}Move Topic{/tr}: {$gContent->mInfo.title|escape}</h1>
	</div>

	<div class="body">
		{form enctype="multipart/form-data" id="editbitboardform"}
			{assign var=title value=$gContent->mInfo.title|escape}
			<input type="hidden" name="ref" value="-" />
			<input type="hidden" name="t" value="{$smarty.request.t}" />
			<div class="form-group">
				{formlabel label="To Board" for="target"}
				{forminput}
					<select name="target" id="target">
						{foreach from=$boards key=content_id item=board_title}
							{if $content_id != $fromBoardId}
								<option value="{$content_id|escape}">{$board_title|escape}</option>
							{/if}
						{foreachelse}
							<option>{tr}No records found{/tr}</option>
						{/foreach}
					</select>
				{/forminput}
			</div>
			<div class="form-group submit">
				<input type="submit" class="btn btn-default" name="move_thread" value="{tr}Move Topic{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .boards -->

{/strip}
