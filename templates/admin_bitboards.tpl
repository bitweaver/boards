{strip}
{form}
	{jstabs}
		{jstab title="Home BitForum"}
			{legend legend="Home BitForum"}
				<input type="hidden" name="page" value="{$page}" />
				<div class="row">
					{formlabel label="Home BitForum (main bitboard)" for="homeBitForum"}
					{forminput}
						<select name="homeBitForum" id="homeBitForum">
							{section name=ix loop=$bitboards}
								<option value="{$bitboards[ix].bitforum_id|escape}" {if $bitforums[ix].bitforum_id eq $home_bitforum}selected="selected"{/if}>{$bitforums[ix].title|escape|truncate:20:"...":true}</option>
							{sectionelse}
								<option>{tr}No records found{/tr}</option>
							{/section}
						</select>
					{/forminput}
				</div>

				<div class="row submit">
					<input type="submit" name="homeTabSubmit" value="{tr}Change preferences{/tr}" />
				</div>
			{/legend}
		{/jstab}

		{jstab title="List Settings"}
			{legend legend="List Settings"}
				<input type="hidden" name="page" value="{$page}" />
				{foreach from=$formBitForumLists key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}

				<div class="row submit">
					<input type="submit" name="listTabSubmit" value="{tr}Change preferences{/tr}" />
				</div>
			{/legend}
		{/jstab}
	{/jstabs}
{/form}
{/strip}
