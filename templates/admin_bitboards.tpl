{strip}
{form}
	{jstabs}
		{jstab title="Home Message Board"}
			{legend legend="Home Message Board"}
				<input type="hidden" name="page" value="{$page}" />
				<div class="row">
					{formlabel label="Home BitBoards (main bitboard)" for="homeBitBoards"}
					{forminput}
						<select name="homeBitBoards" id="homeBitBoards">
							{section name=ix loop=$bitboards}
								<option value="{$bitboards[ix].bitboards_id|escape}" {if $bitboards[ix].bitboards_id eq $home_bitboard}selected="selected"{/if}>{$bitboards[ix].title|escape|truncate:20:"...":true}</option>
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
				{foreach from=$formBitBoardsLists key=item item=output}
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
