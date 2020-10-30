/**
 * @file
 */

(function ($, Drupal) {

  function hideFormElements(element) {
    let row = element.closest('.field--type-entity-reference-date tr');
    row.find('.start_date').hide();
    row.find('.end_date').hide();
    row.find('h4').hide();
  }

  function showFormElements(element) {
    let row = element.closest('.field--type-entity-reference-date tr');
    row.find('.start_date.hidden').css('display', 'inline-block');
    row.find('.end_date.hidden').css('display', 'inline-block');
    row.find('h4').show();
  }

  function showCheckedHideUncheckedFormElements(element) {
    if (element.prop("checked") === true) {
      showFormElements(element);
    }
else if (element.prop("checked") === false) {
      hideFormElements(element);
    }
  }

  Drupal.behaviors.handleCheckboxClick = {
    attach: function () {
      $('.field--widget-entity-reference-autocomplete-date input[type=checkbox]').click(function () {
        showCheckedHideUncheckedFormElements($(this));
      });
    }
  };

  Drupal.behaviors.handleWidgetsWithSelectedDate = {
    attach: function () {
      $('.field--type-entity-reference-date input[type=checkbox]').each(function () {
        showCheckedHideUncheckedFormElements($(this));
      });
    }
  };

})(jQuery, Drupal);
