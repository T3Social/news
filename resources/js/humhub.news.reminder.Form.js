/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

humhub.module('news.reminder.Form', function (module, require, $) {
    var Widget = require('ui.widget').Widget;
    var client = require('client');

    var SELECTOR_ITEMS = '.news-reminder-items';
    var SELECTOR_DEFAULT_ITEMS = '.news-reminder-item-defaults';
    var SELECTOR_REMINDER_TYPE_DROPDOWN = '#remindersettings-remindertype';
    var SELECTOR_ADD_BUTTON = '.btn-primary[data-action-click="add"]';

    var REMINDER_TYPE_NONE = 0;
    var REMINDER_TYPE_DEFAULTS = 1;
    var REMINDER_TYPE_CUSTOM = 2;

    var Form = Widget.extend();

    Form.prototype.init = function(evt) {
        this.checkMaxReminder();
        this.checkReminderType();
    };

    Form.prototype.checkMaxReminder = function() {
        var count = this.$.find(SELECTOR_ITEMS).find('[data-reminder-index]').length;
        if(count >= this.options.maxReminder) {
            this.$.find(SELECTOR_ADD_BUTTON).hide();
        } else {
            this.$.find(SELECTOR_ADD_BUTTON).show();
        }
    };

    Form.prototype.checkReminderType = function() {
        var $reminderType = $(SELECTOR_REMINDER_TYPE_DROPDOWN).val();

        if($reminderType == REMINDER_TYPE_NONE) {
            $(SELECTOR_ITEMS).hide();
            $(SELECTOR_DEFAULT_ITEMS).hide();
        } else if($reminderType == REMINDER_TYPE_CUSTOM) {
            $(SELECTOR_ITEMS).show();
            $(SELECTOR_DEFAULT_ITEMS).hide();
        } else if($reminderType == REMINDER_TYPE_DEFAULTS) {
            $(SELECTOR_ITEMS).hide();
            $(SELECTOR_DEFAULT_ITEMS).show();
        }
    };

    Form.prototype.delete = function(evt) {
        var that = this;
        evt.$trigger.closest('.row').fadeOut('fast', function() {
            $(this).remove();
            that.checkMaxReminder();
        });

    };

    Form.prototype.add = function(evt) {
        var $triggerRow = evt.$trigger.closest('.row');
        var $lastIndex = parseInt($triggerRow.attr('data-reminder-index'));
        var $newRow = $triggerRow.clone().attr('data-reminder-index', ++$lastIndex);

        $newRow.find('[name]').each(function() {
            var name = $(this).attr('name').replace(/NewsReminder\[[0-9]]/, 'NewsReminder['+$lastIndex+']');
            $(this).attr('name', name);
        });

        $newRow.insertAfter($triggerRow);

        evt.$trigger.data('action-click', 'delete')
            .removeClass('btn-primary')
            .addClass('btn-danger')
            .find('i')
            .removeClass('fa-plus')
            .addClass('fa-times');

        this.checkMaxReminder();
    };

    Form.prototype.reset = function(evt) {
        var $settings = this.$.find('.news-reminder-items');
        client.post(evt).then(function(response) {
            $settings.replaceWith($(response.html).find('.news-reminder-items'));
            module.log.success('saved');
        }).catch(function(e) {
            module.log.error(e, true);
        });
    };


    module.export = Form;
});