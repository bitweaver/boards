{assign var=package value='boards'}
{assign var=title value=$package|ucwords}
{strip}
{jstab title=$title}
{form legend=$title}
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
					{html_checkboxes name="boards[$option]" values="y" checked=$value labels=false id="boards[$option]"}
				{/if}
				{formhelp note=`$output.note` page=`$output.page` link=`$output.link`}
			{/forminput}
		</div>
	{/foreach}

	<div class="row submit">
		<input type="submit" name="boards[submit]" value="{tr}Change preferences{/tr}" />
	</div>
{/form}
{/jstab}
{/strip}
