jQuery(document).ready(function(){
  var NO_SELECTED = '0';
  var YES_SELECTED = '1';

  var LINK_TYPE_CONTRIBUTION_PAGE = '0';
  var LINK_TYPE_URL = '1';

  var EVENT_ACCESS_TYPE_MEMBERS_ONLY = '1';
  var EVENT_ACCESS_TYPE_GROUPS_ONLY = '2';

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
    toggleTabFields();

    togglePurchaseButtonFields();

    var purchaseLinkType = jQuery("input[name='purchase_membership_link_type']:checked").val();
    toggleLinkTypeFields(purchaseLinkType);
  }

  /**
   * Sets the fields event listeners
   */
  function setFieldListeners() {
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
    if (eventAccessTypeField.find(':checked').val() === EVENT_ACCESS_TYPE_MEMBERS_ONLY) {
      membersOnlyEventSection.show();
      allowedMembershipTypesField.show();
      allowedGroupsField.hide();
      purchaseMembershipButtonField.show();
    } else if (eventAccessTypeField.find(':checked').val() === EVENT_ACCESS_TYPE_GROUPS_ONLY) {
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
   * Shows contribution selectttion field
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
});
