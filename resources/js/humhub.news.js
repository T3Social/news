/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
humhub.module('news', function (module, require, $) {
    const stream = require('stream');
    const Widget = require('ui.widget').Widget;
    var client = require('client');

    const filterContentType = function (evt) {
        // Reset filters:
        $('#wall-stream-filter-nav [data-filter-category]').each(function () {
            if ($(this).data('ui-widget')) {
                Widget.instance($(this)).reset();
            } else if ($(this).data('filter-type') === 'checkbox') {
                // Just deactivate all checkboxes because they don't have default value:
                $(this).data('filter-input-instance').deactivate();
            }
            // TODO: Reset radio inputs to default value when it will be possible
        });

        // Filter by Content Type:
        stream.wall.WallStreamFilter.prototype.getContentTypePicker().select(evt.$trigger.data('contentType'));
    };

    const confirmReading = function (evt) {
        client.post(evt).then(function (response) {
            const confirmButton = $(evt.$trigger);
            confirmButton.parents(".confirm").replaceWith(response.html);
            //console.log(confirmButton.parent(".confirm"));

            /*
            confirmButton.next('.wall-news-progress-bar').remove();
            confirmButton.after(response.html).remove();
             */
        }).catch(function (err) {
            module.log.error(err, true);
        }).finally(function () {
            evt.finish();
        });
    }

    module.export({
        filterContentType,
        confirmReading,
    });
});
