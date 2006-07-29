{strip}
{jstab title="Boards"}
<div class="admin bitboard">
	<div class="body">
				{form legend="Simple Settings"}
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
				{form enctype="multipart/form-data" id="bitboarduprefs" legend="Edit Signature"}
					{formfeedback warning=$error}
						{include file="bitpackage:liberty/edit_format.tpl" gContent=$signatureContent}

						{if $gBitSystem->isFeatureActive('package_smileys')}
							{include file="bitpackage:smileys/smileys_full.tpl"}
						{/if}

						{if $gBitSystem->isFeatureActive('package_quicktags')}
							{include file="bitpackage:quicktags/quicktags_full.tpl"}
						{/if}

						<div class="row">
							{formlabel label="Signiture"}
							{forminput}
								<textarea {spellchecker} id="{$textarea_id}" name="bitboarduprefs[edit]"  rows="10" cols="50">{$signatureContent->mInfo.data|escape:html}</textarea>
							{/forminput}
						</div>

						<div class="row submit">
							<input type="submit" name="preview" value="{tr}Preview{/tr}" /> <input type="submit" name="save_bitboarduprefs" value="{tr}Save{/tr}" />
						</div>
				{/form}
			{if $preview}
				{legend legend="Preview Signature"}
					<div class="preview">
						{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$signatureContent->mInfo}
						<div class="display bitboard">
							<div class="body">
								<div class="content">
									{$signatureContent->mInfo.parsed_data}
								</div><!-- end .content -->
							</div><!-- end .body -->
						</div><!-- end .bitboard -->
						{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$signatureContent->mInfo}
					</div>
				{/legend}
			{/if}
			{* any service edit template tabs *}
			{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_tab_tpl}
	</div><!-- end .body -->
</div><!-- end .bitboard -->
{/jstab}
{/strip}
