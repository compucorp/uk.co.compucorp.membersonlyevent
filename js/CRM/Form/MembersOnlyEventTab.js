jQuery(document).ready(function(){
  var NO_SELECTED = '0';
  var YES_SELECTED = '1';

  var LINK_TYPE_CONTRIBUTION_PAGE = '0';
  var LINK_TYPE_URL = '1';

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
  var membersOnlyEventSection = jQuery("#members-only-event-section");
  var allowedMembershipTypesField = jQuery("#allowed-membership-types-field");
  var allowedGroupsField = jQuery("#allowed-groups-field");

  var purchaseMembershipButtonField = jQuery("#purchase-membership-button");
  var purchaseButtonDisabledSection = jQuery("#purchase-button-disabled-section");
  var purchaseButtonEnabledSection = jQuery("#purchase-button-enabled-section");

  var contributionPageField = jQuery("#field-contribution-page-id");
  var purchaseURLField = jQuery("#field-purchase-membership-url");

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

    togglePurchaseButtonFields();

    var purchaseLinkType = jQuery("input[name='purchase_membership_link_type']:checked").val();
    toggleLinkTypeFields(purchaseLinkType);
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
        membersOnlyEventSection.hide();
      }
    });

    purchaseMembershipButtonField.click(togglePurchaseButtonFields);

    jQuery("input[name='purchase_membership_link_type']").click(function(){
      toggleLinkTypeFields(jQuery(this).val());
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
      membersOnlyEventSection.show();
      allowedMembershipTypesField.show();
      allowedGroupsField.hide();
      purchaseMembershipButtonField.show();
    } else if (eventAccessTypeValue === EVENT_ACCESS_TYPE_GROUPS_ONLY) {
      membersOnlyEventSection.show();
      allowedMembershipTypesField.hide();
      allowedGroupsField.show();

      // Only shows allowed_groups and notice_for_access_denied fields if the
      // groups_only option was chosen.
      purchaseMembershipButtonField.find('[value=' + NO_SELECTED + ']').prop('checked', true)
      purchaseMembershipButtonField.click();
      purchaseMembershipButtonField.hide();
    } else {
      membersOnlyEventSection.hide();
    }
  }

  /**
   * Shows/Hides the related purchase membership
   * button fields.
   * If the selectedOption was Yes then allow the user to set
   * button label and the link.
   * If the selectedOption was selected then show only the
   * notice message textarea.
   */
  function togglePurchaseButtonFields() {
    var selectedOption = purchaseMembershipButtonField.find(':checked').val()
    switch (selectedOption) {
      case NO_SELECTED:
        purchaseButtonDisabledSection.show();
        purchaseButtonEnabledSection.hide();
        break;
      case YES_SELECTED:
        purchaseButtonDisabledSection.hide();
        purchaseButtonEnabledSection.show();
        break;
    }
  }

  /**
   * Shows contribution selection field
   * and hide the url field or vice-versa
   * based on the selected link type.
   *
   * @param linkType
   */
  function toggleLinkTypeFields(linkType) {
    switch (linkType) {
      case LINK_TYPE_CONTRIBUTION_PAGE:
        contributionPageField.show();
        purchaseURLField.hide();
        break;
      case LINK_TYPE_URL:
        contributionPageField.hide();
        purchaseURLField.show();
        break;
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
