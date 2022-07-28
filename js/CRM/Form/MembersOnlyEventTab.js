jQuery(document).ready(function(){
  var NO_SELECTED = '0';
  var YES_SELECTED = '1';

  var EVENT_ACCESS_TYPE_MEMBERS_ONLY = CRM.vars.MembersOnlyEvent.EVENT_ACCESS_TYPE_MEMBERS_ONLY;
  var EVENT_ACCESS_TYPE_GROUPS_ONLY = CRM.vars.MembersOnlyEvent.EVENT_ACCESS_TYPE_GROUPS_ONLY;
  var EVENT_ACCESS_TYPE_AUTHENTICATED_ONLY = CRM.vars.MembersOnlyEvent.EVENT_ACCESS_TYPE_AUTHENTICATED_ONLY;

  var customAccessDeniedMessageCheckbox = jQuery("#is_showing_custom_access_denied_message");
  var customAccessDeniedMessageSection = jQuery("#notice_for_access_denied").closest('.crm-section');

  var loginBlockMessageCheckbox = jQuery("#is_showing_login_block");
  var loginBlockTypeSection = jQuery("#block_type").closest('.crm-section');
  var loginBlockMessageSection = jQuery("#login_block_message").closest('.crm-section');

  var purchaseMembershipCheckbox = jQuery("#is_showing_purchase_membership_block");
  var purchaseMembershipButtonSection = jQuery("#purchase_membership_button_label").closest('.crm-section');
  var purchaseMembershipBodyTextSection = jQuery("#purchase_membership_body_text").closest('.crm-section');
  var contributionPageSection = jQuery("#contribution_page_id").closest('.crm-section');

  var eventAccessTypeField = jQuery("#event-access-type");
  var allowedMembershipTypesField = jQuery("#allowed-membership-types-field");
  var allowedGroupsField = jQuery("#allowed-groups-field");

  setInitialFieldValues();
  setFieldListeners();

  /**
   * Sets the initial field values and show/hide
   * the needed fields.
   */
  function setInitialFieldValues() {
    toggleCustomAccessDeniedMessageField();

    toggleLoginBlockFields();

    togglePurchaseMembershipFields();

    toggleTabFields();
  }

  /**
   * Sets the fields event listeners
   */
  function setFieldListeners() {
    customAccessDeniedMessageCheckbox.change(toggleCustomAccessDeniedMessageField);

    loginBlockMessageCheckbox.change(toggleLoginBlockFields);

    purchaseMembershipCheckbox.change(togglePurchaseMembershipFields);

    eventAccessTypeField.change(toggleTabFields);

    eventAccessTypeField.click(function(e){
      // Checks if target is the crm-clear-link.
      if (jQuery(e.target).hasClass('crm-clear-link') || jQuery(e.target).hasClass('fa-times')) {
        allowedMembershipTypesField.hide();
        allowedGroupsField.hide();
      }
    });
  }

  /**
   * Shows/Hides the tab fields
   * based on 'Is members-only event ?' checkbox
   * value.
   */
  function toggleTabFields() {
    var eventAccessTypeValue = parseInt(eventAccessTypeField.find(':checked').val());
    if (eventAccessTypeValue === EVENT_ACCESS_TYPE_MEMBERS_ONLY) {
      allowedMembershipTypesField.show();
      allowedGroupsField.hide();
    } else if (eventAccessTypeValue === EVENT_ACCESS_TYPE_GROUPS_ONLY) {
      allowedMembershipTypesField.hide();
      allowedGroupsField.show();
    } else {
      allowedMembershipTypesField.hide();
      allowedGroupsField.hide();
    }
  }

  /**
   * Shows/Hides the related custom access denied
   * message field.
   */
  function toggleCustomAccessDeniedMessageField() {
    if (customAccessDeniedMessageCheckbox.is(':checked')) {
      customAccessDeniedMessageSection.show();
    } else {
      customAccessDeniedMessageSection.hide();
    }
  }

  /**
   * Shows/Hides the related login block fields.
   */
  function toggleLoginBlockFields() {
    if (loginBlockMessageCheckbox.is(':checked')) {
      loginBlockTypeSection.show();
      loginBlockMessageSection.show();
    } else {
      loginBlockTypeSection.hide();
      loginBlockMessageSection.hide();
    }
  }

  /**
   * Shows/Hides the related membership block fields.
   */
  function togglePurchaseMembershipFields() {
    if (purchaseMembershipCheckbox.is(':checked')) {
      purchaseMembershipButtonSection.show();
      purchaseMembershipBodyTextSection.show();
      contributionPageSection.show();
    } else {
      purchaseMembershipButtonSection.hide();
      purchaseMembershipBodyTextSection.hide();
      contributionPageSection.hide();
    }
  }
});
