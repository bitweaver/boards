{* $Header: /cvsroot/bitweaver/_bit_boards/templates/board_edit.tpl,v 1.8 2008/04/14 09:01:41 squareing Exp $ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin boards">
	{if $preview}
		<h2>Preview {$gContent->mInfo.title|escape}</h2>
		<div class="preview">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}
				<div class="display boards">
					<div class="header">
						<h1>{$gContent->mInfo.title|escape|default:"Board"}</h1>
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

						<div class="row">
							{formlabel label="Title" for="title"}
							{forminput}
								<input type="text" size="50" maxlength="200" name="bitboard[title]" id="title" value="{$gContent->getTitle()|escape}" />
							{/forminput}
						</div>

						{textarea name="bitboard[edit]"}{$gContent->mInfo.data}{/textarea}

						{* any simple service edit options *}
						{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_mini_tpl}

						<div class="row">
							{formlabel label="Board Sync" for="boardsync"}
							{forminput}
						{if $gBitSystem->getConfig('boards_sync_mail_server')}
								<input type="text" size="50" maxlength="200" name="bitboardconfig[board_sync_list_address]" id="board_sync_list_address" value="{$gContent->getPreference('board_sync_list_address')|escape}" />
								{formhelp note="Messages will be sent and recieved to the email address above."}
						{else}
							{tr}Board Sync is not available since the Board Sync master email box has not been configured.{/tr} {if !$gBitUser->isAdmin()}{tr}Check with your site administrator.{/tr}{/if}
						{/if}
								{if $gBitUser->isAdmin()}{tr}See the global <a href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=boards">Board Settings</a> for master email box configuration.{/tr}{/if}
							{/forminput}
						</div>

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
</div><!-- end .boards -->

{/strip}
