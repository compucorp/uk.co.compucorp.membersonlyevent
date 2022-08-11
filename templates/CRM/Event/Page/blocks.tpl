{if !empty($snippet.membersOnlyEvent.is_showing_custom_access_denied_message)}
  <br>
  <hr>
  <div class="crm-section access_denied-section">
    {$snippet.membersOnlyEvent.notice_for_access_denied}
  </div>
{/if}

{if !empty($snippet.membersOnlyEvent.is_showing_login_block)}
  <br>
  <hr>
  <div class="crm-section login-section">
    {$snippet.membersOnlyEvent.login_block_header}
    {$snippet.membersOnlyEvent.login_block_message}
    {$snippet.membersOnlyEvent.login_block_content}
  </div>
{/if}
