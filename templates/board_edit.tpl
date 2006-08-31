{* $Header: /cvsroot/bitweaver/_bit_boards/templates/board_edit.tpl,v 1.2 2006/07/29 17:14:26 spiderr Exp $ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin bitboard">
	{if $preview}
		<h2>Preview {$gContent->mInfo.title|escape}</h2>
		<div class="preview">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}
				<div class="display bitboard">
					<div class="header">
						<h1>{$gContent->mInfo.title|escape|default:"Board"}</h1>
					</div><!-- end .header -->
					<div class="body">
						<div class="content">
							{$gContent->mInfo.parsed_data}
						</div><!-- end .content -->
					</div><!-- end .body -->
				</div><!-- end .bitboard -->
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

						<div class="row">
							{formlabel label="Title" for="title"}
							{forminput}
								<input type="text" size="60" maxlength="200" name="bitboard[title]" id="title" value="{$gContent->mInfo.title|escape}" />
							{/forminput}
						</div>

						{include file="bitpackage:liberty/edit_format.tpl"}

						{if $gBitSystem->isFeatureActive('package_smileys')}
							{include file="bitpackage:smileys/smileys_full.tpl"}
						{/if}

						{if $gBitSystem->isFeatureActive('package_quicktags')}
							{include file="bitpackage:quicktags/quicktags_full.tpl"}
						{/if}

						<div class="row">
							{forminput}
								<textarea {spellchecker} id="{$textarea_id}" name="bitboard[edit]" rows="{$smarty.cookies.rows|default:10}" cols="50">{$gContent->mInfo.data|escape:html}</textarea>
							{/forminput}
						</div>

						{* any simple service edit options *}
						{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_mini_tpl}

						<div class="row submit">
							<input type="submit" name="preview" value="{tr}Preview{/tr}" />
							<input type="submit" name="save_bitboard" value="{tr}Save{/tr}" />
						</div>
					{/legend}
				{/form}
			{/jstab}
			{* any service edit template tabs *}
			{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_tab_tpl}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .bitboard -->

{/strip}