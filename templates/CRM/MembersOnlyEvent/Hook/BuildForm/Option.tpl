<table>
  <tbody id="additional-sign-up">
    <tr class="crm-price-option-entity_table">
      <td class="label"><label>{ts}Additional Signup?{/ts}</label></td>
      <td>{$form.entity_table.html}</td>
    </tr>
    <tr class="crm-price-option-membership_types">
      <td class="label"><label>{ts}Membership Type{/ts}</label></td>
      <td>{$form.membership_types.html}</td>
    </tr>
    <tr class="crm-price-option-events">
      <td class="label"><label>{ts}Event{/ts}</label></td>
      <td>{$form.events.html}</td>
    </tr>
  </tbody>
</table>

{literal}
<script>
CRM.$(function($) {
  $('#additional-sign-up > *').insertAfter('.crm-price-option-form-block-is_default');

  var entityTableSelect= $("select[name=entity_table]");
  var membershipTypesSelect = $(".crm-price-option-membership_types ");
  var eventsSelect = $(".crm-price-option-events");

  setInitialFieldValues();
  setFieldListeners();

  /**
   * Sets the initial field values and show/hide
   * the needed fields.
   */
  function setInitialFieldValues() {
    toggleSelectFields(entityTableSelect.val());
  }

  /**
   * Sets the fields event listeners
   */
  function setFieldListeners() {
    entityTableSelect.change(function(){
      toggleSelectFields($(this).val());
    });
  }

  /**
   * Shows/Hides the events/membership_types selector
   * based on 'entity_table' select value.
   */
  function toggleSelectFields(entityTable) {
    membershipTypesSelect.hide();
    eventsSelect.hide();
    if (entityTable === 'Membership'){
      membershipTypesSelect.show();
    } else if (entityTable === 'Participant'){
      eventsSelect.show();
    }
  }
});
</script>
{/literal}
