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

{if !empty($snippet.membersOnlyEvent.is_showing_purchase_membership_block)}
  <br>
  <hr>
  <div class="crm-section purchase_membership-section">
    <div class="label">{$snippet.membersOnlyEvent.purchase_membership_button_label}</div>
    <div class="content">
      {$snippet.membersOnlyEvent.purchase_membership_body_text}
      <a href="{$snippet.membersOnlyEvent.purchase_membership_url}" class="button">{$snippet.membersOnlyEvent.purchase_membership_button_label}</a>
    </div>
    <div class="clear"></div>
  </div>
  <br>
  <br>
{/if}
