{strip}
{jstab title=$smarty.const.BOARDS_PKG_DIR|ucfirst}
<div class="admin boards">
	<div class="body">
		{if $boardsSettings}
		{form legend="Simple Settings"}
			<input type="hidden" name="view_user" value="{$view_user}" />
			{foreach from=$boardsSettings key=option item=output}
				<div class="row">
					{assign var='pref' value=$output.pref}
					{if $userPrefs.$pref == null}
						{assign var='value' value=$output.default}
					{else}
						{assign var='value' value=$userPrefs.$pref}
					{/if}
					{formlabel label=$output.label for=$option}
					{forminput}
						{if $output.type == 'text'}
							<input type="text" maxlength="250" size="250" name="boards[{$option}]" id="boards[{$option}]" value="{$value|escape}" />
						{else}
							<input type="checkbox" name="boards[{$option}]" {if $value=='y'}checked="checked"{/if} value="y" id="boards[{$option}]" />
						{/if}
						{formhelp note=`$output.note` page=`$output.page` link=`$output.link`}
					{/forminput}
				</div>
			{/foreach}
			<div class="row submit">
				<input type="submit" name="boards[submit]" value="{tr}Change Settings{/tr}" />
			</div>
		{/form}
		{/if}

		{form enctype="multipart/form-data" id="bitboarduprefs" legend="Edit Signature"}
			<input type="hidden" name="view_user" value="{$view_user}" />
			{formfeedback warning=$error}
			{textarea gContent=$signatureContent name=bitboarduprefs[edit]" rows=4}{$editUser->getPreference('signature_content_data')}{/textarea}
			<div class="row submit">
				<input type="submit" name="preview" value="{tr}Preview{/tr}" /> <input type="submit" name="save_bitboarduprefs" value="{tr}Save{/tr}" />
			</div>
		{/form}

		{if $preview}
			{legend legend="Preview Signature"}
				<div class="preview">
					{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$signatureContent->mInfo}
					<div class="display boards">
						<div class="body">
							<div class="content">
								{$signatureContent->mInfo.parsed_data}
							</div><!-- end .content -->
						</div><!-- end .body -->
					</div><!-- end .boards -->
					{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$signatureContent->mInfo}
				</div>
			{/legend}
		{/if}
		{* any service edit template tabs *}
	</div><!-- end .body -->
</div><!-- end .boards -->
{/jstab}
{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_tab_tpl"}
{/strip}
