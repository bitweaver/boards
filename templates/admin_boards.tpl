{strip}
{form}
{*
	{legend legend="Home Message Board"}
		<input type="hidden" name="page" value="{$page}" />
		<div class="control-group">
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

		<div class="control-group submit">
			<input type="submit" class="btn" name="homeTabSubmit" value="{tr}Change preferences{/tr}" />
		</div>
	{/legend}
*}
	{legend legend="List Settings"}
		<input type="hidden" name="page" value="{$page}" />
		{foreach from=$formBitBoardsLists key=item item=output}
			<div class="control-group">
				{formlabel label=`$output.label` for=$item}
				{forminput}
					{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
					{formhelp note=`$output.note` page=`$output.page`}
				{/forminput}
			</div>
		{/foreach}
	{/legend}

	{legend legend="Mailing List Synchronization"}
		<div class="control-group">
			{forminput}
		<h2>List to Board Sync</h2>
		<p>{tr}List to Board Sync allows a bitweaver board to mirror messages that are posted to a mailing list. A single, master email inbox entered below is used for *all* email list subscriptions. Then, configure individual boards to indicate which mailing list to which it is subscribed. The Board Sync cron script will import messages from the master email inbox.{/tr}</p>
			{/forminput}
		</div>
		{foreach from=$formBitBoardsSync key=item item=output}
			<div class="control-group">
				{formlabel label=`$output.label` for=$item}
				{forminput}
					<input type="text" name="{$item}" value="{$gBitSystem->getConfig($item)}" id={$item}" />
					{formhelp note=`$output.note` page=`$output.page`}
				{/forminput}
			</div>
		{/foreach}

		<div class="control-group">
			{forminput}
		<h2>Board to List Sync</h2>
		<p>{tr}Board to List Sync is the opposite of the above. It allows a bitweaver board to send a message to mailing list. You must complete the "List to Board Sync" configuration above for this feature to work.{/tr} {tr}Please see <a href="http://www.bitweaver.org/wiki/BoardsEmailListConfig">configuration requirements</a> prior to utilizing this feature.{/tr}</p>
			{/forminput}
		</div>

		<div class="control-group">
			{forminput}
				
			{/forminput}
		</div>

		{foreach from=$formGroupEmailList key=item item=output}
			<div class="control-group">
				{formlabel label=`$output.label` for=$item}
				{forminput}
					{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
					{formhelp note=`$output.note` page=`$output.page`}
				{/forminput}
			</div>
		{/foreach}

		<div class="control-group">
			{formlabel label="Email Host" for='boards_email_host'}
			{forminput}
				<input type="text" name="boards_email_host" value="{$gBitSystem->getConfig('boards_email_host',$gBitSystem->getConfig('kernel_server_name'))}" />
				{formhelp note="Enter the host name to which all mailing lists will be hosted and addressed."}
			{/forminput}
		</div>
		<div class="control-group">
			{formlabel label="Administrator Email" for='boards_email_admin'}
			{forminput}
				<input type="text" name="boards_email_admin" value="{$gBitSystem->getConfig('boards_email_admin',$gBitSystem->getConfig('site_sender_email'))}" />
				{formhelp note="This is the email for the master administrator for all mailing lists."}
			{/forminput}
		</div>

		<div class="control-group">
			{formlabel label="Mailman bin Path" for='server_mailman_bin'}
			{forminput}
				<input type="text" name="server_mailman_bin" value="{$gBitSystem->getConfig('server_mailman_bin')|escape}" />
				{formhelp note="Path to mailman applications, typically: /usr/lib/mailman/bin/"}
				{if !$gBitSystem->getConfig('server_mailman_bin')}
					{formfeedback error="This setting is required to use mailing lists."}
				{/if}
			{/forminput}
		</div>
		<div class="control-group">
			{formlabel label="Mailman Command Path" for='mailman_cmd'}
			{forminput}
				<input type="text" name="server_mailman_cmd" value="{$gBitSystem->getConfig('server_mailman_cmd', '/usr/lib/mailman/mail/mailman')|escape}" />
				{formhelp note="Path to mailman binary, typically: /usr/lib/mailman/mail/mailman"}
			{/forminput}
		</div>
		<div class="control-group">
			{formlabel label="Newaliases command" for='server_newaliases_cmd'}
			{forminput}
				<input type="text" name="server_newaliases_cmd" value="{$gBitSystem->getConfig('server_newaliases_cmd', '/usr/bin/newaliases')|escape}" />
				{formhelp note="Path to the command to tell the mail server to reload the aliases file. For sendmail typically: /usr/bin/newaliases"}
			{/forminput}
		</div>
		<div class="control-group">
			{formlabel label="MTA Aliases Map" for='server_aliases_file'}
			{forminput}
				<input type="text" name="server_aliases_file" value="{$gBitSystem->getConfig('server_aliases_file', '/etc/aliases')|escape}" />
				{formhelp note="Path to the command to tell the mail server to reload the aliases file. For sendmail typically: /usr/bin/newaliases"}
			{/forminput}
		</div>
	{/legend}
	<div class="control-group submit">
		<input type="submit" class="btn" name="listTabSubmit" value="{tr}Change preferences{/tr}" />
	</div>
{/form}
{/strip}
