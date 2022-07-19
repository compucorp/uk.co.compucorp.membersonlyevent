<div class="crm-block crm-form-block crm-event-manage-membersonlyevent-form-block" style="display: none;">
  {* HEADER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  <div class="crm-section" id="event-access-type">
    <div class="label">{$form.event_access_type.label}</div>
    <div class="content">
      {$form.event_access_type.html}
    </div>
    <div class="clear"></div>
  </div>

  <div id="members-only-event-section">
    <div class="crm-section" id="allowed-membership-types-field">
      <div class="label">{$form.allowed_membership_types.label} {help id="allowed-membership-types" file="CRM/MembersOnlyEvent/Form/MembersOnlyEventTab"}</div>
      <div class="content">{$form.allowed_membership_types.html}</div>
      <div class="clear"></div>
    </div>

    <div class="crm-section" id="allowed-groups-field">
      <div class="label">{$form.allowed_groups.label} {help id="allowed-groups" file="CRM/MembersOnlyEvent/Form/MembersOnlyEventTab"}</div>
      <div class="content">{$form.allowed_groups.html}</div>
      <div class="clear"></div>
    </div>

    <div class="crm-section" id="purchase-membership-button">
      <div class="label">{$form.purchase_membership_button.label}</div>
      <div class="content">{$form.purchase_membership_button.html}</div>
      <div class="clear"></div>
    </div>

    <div id="purchase-button-disabled-section">
      <div class="crm-section">
        <div class="label">{$form.notice_for_access_denied.label}</div>
        <div class="content">{$form.notice_for_access_denied.html}</div>
        <div class="clear"></div>
      </div>
    </div>

    <div id="purchase-button-enabled-section">
      <div class="crm-section">
        <div class="label">{$form.purchase_membership_button_label.label}</div>
        <div class="content">{$form.purchase_membership_button_label.html}</div>
        <div class="clear"></div>
      </div>

      <div class="crm-section">
        <div class="label">{$form.purchase_membership_link_type.label}</div>
        <div class="content">
          {$form.purchase_membership_link_type.html}
          <span id="field-contribution-page-id">{$form.contribution_page_id.html}</span>
          <span id="field-purchase-membership-url">{$form.purchase_membership_url.html}</span>
        </div>
        <div class="clear"></div>
      </div>
    </div>
  </div>

  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
<div class="crm-event-manage-membersonlyevent-form-help">
  <div class="help">{ts}Online registration tab needs to be enabled first.{/ts}</div>
</div>
{literal}
<script>
  CRM.$(function($) {

    initialPage();
    eventListner();

    function initialPage() {
      //Check if the online registration for this event is allowed, show notification message otherwise
      toggleForm();
    }

    function eventListner() {
      $("#tab_membersonlyevent").click(function(){
        toggleForm();
      });
    }

    function toggleForm() {
      CRM.api3('Event', 'get', {
        "sequential": 1,
        "return": ["is_online_registration"],
        "id": {/literal}{$id}{literal}
      }).then(function(result) {
        if (result['values'][0]['is_online_registration'] == 1) {
          $(".crm-event-manage-membersonlyevent-form-block").show();
          $(".crm-event-manage-membersonlyevent-form-help").hide();
          $("#tab_membersonlyevent").removeClass("disabled");
        }
        else {
          $(".crm-event-manage-membersonlyevent-form-block").hide();
          $(".crm-event-manage-membersonlyevent-form-help").show();
          $("#tab_membersonlyevent").addClass("disabled");
        }
      });
    }
  });
</script>
{/literal}
{crmScript ext="uk.co.compucorp.membersonlyevent" file="js/CRM/Form/MembersOnlyEventTab.js"}
{crmStyle ext="uk.co.compucorp.membersonlyevent" file="css/MembersOnlyEventTab.css"}
