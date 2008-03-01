{strip}
{form}
{*
	{legend legend="Home Message Board"}
		<input type="hidden" name="page" value="{$page}" />
		<div class="row">
			{formlabel label="Home BitBoards (main board)" for="homeBitBoards"}
			{forminput}
				<select name="homeBitBoards" id="homeBitBoards">
					{section name=ix loop=$boards}
						<option value="{$boards[ix].boards_id|escape}" {if $boards[ix].boards_id eq $home_bitboard}selected="selected"{/if}>{$boards[ix].title|escape|truncate:20:"...":true}</option>
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
*}
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

		<div class="row">
			{forminput}
		<h2>Board Sync</h2>
		<p>{tr}Board Sync allows a bitweaver board to be synchronized with a mailing list. A single mailbox is used to archive all email messages, so you subscribe *all* email lists using the same email address as entered below. Then, configure individual boards to indicate which mailing list to which it is subscribed. The Board Sync cron script will import messages from the email inbox.{/tr}</p>
			{/forminput}
		</div>
		{foreach from=$formBitBoardsSync key=item item=output}
			<div class="row">
				{formlabel label=`$output.label` for=$item}
				{forminput}
					<input type="text" name="{$item}" value="{$gBitSystem->getConfig($item)}" id={$item}" />
					{formhelp note=`$output.note` page=`$output.page`}
				{/forminput}
			</div>
		{/foreach}

		<div class="row submit">
			<input type="submit" name="listTabSubmit" value="{tr}Change preferences{/tr}" />
		</div>
	{/legend}
{/form}
{/strip}
