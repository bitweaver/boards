{strip}

<div class="listing users">
	<div class="header">
		<h1>{tr}Mailing List{/tr}: {$board->getDisplayLink()}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}
{jstabs}

{jstab title="Mailing List Information"}
{if $board->getPreference('boards_mailing_list')}
	<div class="row">
		{formlabel label="Address"}
		{forminput}
			{$board->getPreference('boards_mailing_list')}@{$gBitSystem->getConfig('boards_email_host',$gBitSystem->getConfig('kernel_server_name'))}
		{/forminput}
	</div>
	{form}
	<input type="hidden" name="b" value="{$board->getField('board_id')}"/>
	<div class="row">
		{formlabel label="Subscribe"}
		{forminput}
			{if mailman_findmember($board->getPreference('boards_mailing_list'),$gBitUser->getField('email'))}
				<p>{tr}You are currently subscribed to the mailing list using the email:{/tr} {$gBitUser->getField('email')}</p>
				<input type="submit" name="unsubscribe" value="Unsubscribe" />
			{else}
				<p>{tr}You are currently not subscribed to the mailing list.{/tr}</p>
				<input type="submit" name="subscribe" value="Subscribe" />
			{/if}
		{/forminput}
	</div>
	{/form}
{else}
			{formfeedback warning="No mailing address has been configured for this group."}
{/if}

{/jstab}

{if $gContent->hasAdminPermission()}
{if $listMembers}
{jstab title="List Subscribers"}
		<ol class="data">
			{foreach from=$listMembers key=userId item=member}
				<li>{displayname email=$member} &lt;{$member}&gt;</li>
			{foreachelse}
				<li>{tr}The group has no members.{/tr}</li>
			{/foreach}
		</ol>
{/jstab}
{/if}
{/if}

{if $gBitUser->hasPermission('p_boards_admin')}
{jstab title="List &rArr; Board Configuration"}
	<div>
		{formlabel label="Introduction" for="boardsync"}
		{forminput}
	This configuration enables all posts to an internet mailing list to be mirrored on the board. To do this, a standard email account is subscribed to the list. A monitoring program then checks the inbox for any posts to the "Mailing List Address" as configured below.
		{/forminput}
	</div>
	{form}
	<input type="hidden" name="b" value="{$board->getField('board_id')}"/>
	{if $gBitSystem->getConfig('boards_sync_mail_server')}
	<div class="row">
		{formlabel label="Mailing List Address" for="boardsync"}
		{forminput}
			<input type="text" size="50" maxlength="200" name="board_sync_list_address" id="board_sync_list_address" value="{$gContent->getPreference('board_sync_list_address')|escape}" />
			{if $boardsMailingList && $boardsMailingList != $gContent->getPreference('board_sync_list_address')}
				{formfeedback warning="The Mailing List Address does not match the configured board mailing list."}
			{/if}
			{formhelp note="All messages posted to this email address will mirrored on the board. The 'Board Sync Inbox' email account must be subscribed to this list and receive the messages in its INBOX."}
			<input type="submit" name="save_list_address" value="Save" />
		{/forminput}
	</div>
	<div>
		{formlabel label="Board Sync Inbox" for="boardsync"}
		{forminput}
			{$gBitSystem->getConfig('boards_sync_user')}@{$gBitSystem->getConfig('boards_sync_mail_server')}
			{formhelp note="This email address should be subscribed to the mailing list like a normal user."}
		{/forminput}
	</div>
{if $board->getPreference('boards_mailing_list')}
	<div class="row">
		{formlabel label="Subscribe"}
		{forminput}
			{if mailman_findmember($board->getPreference('boards_mailing_list'),$boardSyncInbox)}
				<p>{$boardSyncInbox} {tr}is subscribed to{/tr} {$boardsMailingList}</p>
				<input type="submit" name="unsubscribe_boardsync" value="Unsubscribe" />
			{else}
				<p>{$boardSyncInbox} {tr}is currently not subscribed to the mailing list.{/tr}</p>
				<input type="submit" name="subscribe_boardsync" value="Subscribe" />
			{/if}
		{/forminput}
	</div>
{/if}
	{else}
	<div class="row">
		{formlabel label="Board Sync" for="boardsync"}
		{forminput}
		{tr}Board Sync is not available since the Board Sync master email box has not been configured.{/tr} {if !$gBitUser->isAdmin()}{tr}Check with your site administrator.{/tr}{/if}
			{if $gBitUser->isAdmin()}{tr}See the global <a href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=boards">Board Settings</a> for master email box configuration.{/tr}{/if}
		{/forminput}
	</div>
	{/if}
	{/form}
{/jstab}

{jstab title="Board &rArr; List Configuration"}
	{form}
	<input type="hidden" name="b" value="{$board->getField('board_id')}"/>
{if $board->getPreference('boards_mailing_list')}
	<div class="row">
		{formlabel label="Address"}
		{forminput}
			{$board->getPreference('boards_mailing_list')}@{$gBitSystem->getConfig('boards_email_host',$gBitSystem->getConfig('kernel_server_name'))}
		{/forminput}
	</div>
	<div class="row submit">
		{forminput}
			<input type="submit" name="delete_list" value="Delete List" />
		{/forminput}
	</div>
	<div class="row">
		{formlabel label="Advanced Configuration"}
		{forminput}
			<a href="{$gBitSystem->getConfig('boards_mailman_uri',"`$smarty.const.BIT_ROOT_URI`mailman/")}admin/{$board->getPreference('boards_mailing_list')}">
			{$gBitSystem->getConfig('boards_mailman_uri',"`$smarty.const.BIT_ROOT_URI`mailman/")}admin/{$board->getPreference('boards_mailing_list')}</a>
		{/forminput}
	</div>
{else}
	{if $gBitSystem->getConfig('server_mailman_bin') && $gBitSystem->getConfig('boards_sync_user') && $gBitSystem->getConfig('boards_sync_mail_server')}
{legend legend="Group Mailing List"}
	<input type="hidden" name="b" value="{$board->getField('board_id')}"/>
	<div class="row">
		{formlabel label="Mailing List Address" for='emailhost'}
		{forminput}
			<input type="text" name="boards_mailing_list" value="{$smarty.request.boards_mailing_list|default:$suggestedListName}" /> <strong> @ {$gBitSystem->getConfig('boards_email_host',$gBitSystem->getConfig('kernel_server_name'))} </strong>
			{formhelp note="This is the email address for the group. It needs to be all lowercase alpha-numeric characters."}
		{/forminput}
	</div>
	<div class="row">
		{formlabel label="Administrator Password" for='emailhost'}
		{forminput}
			<input type="text" name="boards_mailing_list_password" value="{$smarty.request.boards_mailing_list_password}" />
			{formhelp note="This is the password used to administer the mailing list."}
		{/forminput}
	</div>
	<div class="row submit">
		{forminput}
			<input type="submit" name="create_list" value="Create List" />
		{/forminput}
	</div>
{/legend}
	{else}
		{if !$gBitSystem->getConfig('server_mailman_bin')}
			{formfeedback error="Mailman is not configured."}
		{/if}
		{if !$gBitSystem->getConfig('boards_sync_user') || !$gBitSystem->getConfig('boards_sync_user')}
			{formfeedback error="List to Board Sync is not configured."}
		{/if}
		{if $gBitUser->isAdmin()}
			<a href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=boards">{tr}See Boards Administration{/tr}</a>
		{/if}
	{/if}
{/if}
	{/form}
{/jstab}
{/if}

{/jstabs}
	</div><!-- end .body -->
</div>
{/strip}

