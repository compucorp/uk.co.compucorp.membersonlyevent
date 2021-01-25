<table>
  <tbody id="additional-sign-up">
    {assign var=arr value=1|range:$field_option_count}
    {foreach from=$arr item=i}
      <td id='entity_table-{$i}'>{$form.entity_table[$i].html}</td>
      <td id='entity_selectors-{$i}'>
        <div id='membership_types-{$i}'>{$form.membership_types[$i].html}</div>
        <div id='events-{$i}'>{$form.events[$i].html}</div>
      </td>
    {/foreach}
  </tbody>
</table>

{literal}
<script>
CRM.$(function($) {
  $('#optionField > tbody > tr:nth-child(1) > th:nth-child(8)').after(
    '<th colspan="2">'+ts('Additional Signup?')+'</th>'
  );

  $('#optionField tr.form-item').each( function(index) {
    $(this).children('td:nth-child(8)').after($('#entity_selectors-'+(index+1)));
    $(this).children('td:nth-child(8)').after($('#entity_table-'+(index+1)));

    var entityTableSelect = $(this).find('select[name="entity_table['+(index+1)+']"]');
    toggleSelectFields(entityTableSelect);
    entityTableSelect.change(function() {
      toggleSelectFields($(this));
    })
  })

  /**
   * Shows/Hides the additional-sign-up td/th
   * based on 'html_type' select value.
   */
  $('select[name="html_type"]').change(function() {
    var isRadio = ($(this).val() === 'Radio')
    var isCheckBox = ($(this).val() === 'CheckBox')

    jQuery('#optionField th:nth-child(9)').hide();
    jQuery('#optionField td:nth-child(9)').hide();
    jQuery('#optionField td:nth-child(10)').hide();

    if (isRadio || isCheckBox) {
      jQuery('#optionField th:nth-child(9)').show();
      jQuery('#optionField td:nth-child(9)').show();
      jQuery('#optionField td:nth-child(10)').show();
    }
  })

  /**
   * Shows/Hides the events/membership_types selector
   * based on 'entity_table' select value.
   */
  function toggleSelectFields(entityTableSelect) {
    var membershipTypesSelect = entityTableSelect.closest('tr').find('div[id^=membership_types]');
    var eventsSelect = entityTableSelect.closest('tr').find('div[id^=events]');

    membershipTypesSelect.hide();
    eventsSelect.hide();

    if (entityTableSelect.val() === 'Membership'){
      membershipTypesSelect.show();
    } else if (entityTableSelect.val() === 'Participant'){
      eventsSelect.show();
    }
  }
});
</script>
{/literal}
