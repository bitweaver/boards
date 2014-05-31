{* $Header$ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin boards">
	{if $preview}
		<h2>Preview {$gContent->getTitle()|escape}</h2>
		<div class="preview">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}
				<div class="display boards">
					<div class="header">
						<h1>{$gContent->getTitle()|escape|default:"Board"}</h1>
					</div><!-- end .header -->

					<div class="body">
						<div class="content">
							{$gContent->mInfo.parsed_data}
						</div><!-- end .content -->
					</div><!-- end .body -->
				</div><!-- end .boards -->
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}
		</div>
	{/if}

	<div class="header">
		<h1>
			{if $gContent->isValid()}
				{tr}{tr}Edit{/tr} {$gContent->getTitle()|escape}{/tr}
			{else}
				{tr}Create New Board{/tr}
			{/if}
		</h1>
	</div>

	<div class="body">
		{jstabs}
			{if $gContent->isValid()}
				{assign var='leg' value=$gContent->getTitle()|escape}
				{assign var='leg' value="Edit Board: $leg"}
			{else}
				{assign var='leg' value='Create Board'}
			{/if}
			{jstab title=$leg}
				{form enctype="multipart/form-data" id="editbitboardform"}
					{legend legend=$leg}
						<input type="hidden" name="bitboard[board_id]" value="{$gContent->mInfo.board_id}" />

						<div class="control-group column-group gutters">
							{formlabel label="Title" for="title"}
							{forminput}
								<input type="text" size="50" maxlength="200" name="bitboard[title]" id="title" value="{$gContent->getTitle()|escape}" />
							{/forminput}
						</div>

						{textarea name="bitboard[edit]" edit=$gContent->mInfo.data}

						{* any simple service edit options *}
						{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"}

					{/legend}
				{/form}
			{/jstab}
			{* any service edit template tabs *}
			{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_tab_tpl"}
		{/jstabs}

		<div class="control-group submit">
			<input type="submit" class="btn btn-primary" name="save_bitboard" value="{tr}Save{/tr}" /> <input type="submit" class="ink-button" name="preview" value="{tr}Preview{/tr}" />
		</div>
	</div><!-- end .body -->
</div><!-- end .boards -->

{/strip}
