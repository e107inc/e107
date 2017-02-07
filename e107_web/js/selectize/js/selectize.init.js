// If you don't insert this line into your JS, you may see the error: e107 is not defined.
var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($) {

    /**
     * Behavior to initialize click event on inline edit (selectize) fields.
     *
     * @type {{attach: e107.behaviors.selectizeEditableInit.attach}}
     */
    e107.behaviors.selectizeEditableInit = {
        attach: function (context, settings) {
            if (e107.settings.selectize) {
                $.each(e107.settings.selectize, function (index, item) {
                    // Inline, popup editor. Initialize selectize after opening popup.
                    if (item.options.e_editable) {
                        $(context).find('#' + item.options.e_editable).once('selectize-editable-init').each(function () {
                            var $eEditable = $('#' + item.options.e_editable);

                            $eEditable.click(function () {
                                // Attach success callback for replacing user id with name.
                                $.fn.editable.defaults.success = function (response, newValue) {
                                    if (response.status == 'error') return;
                                    if ($eEditable.hasClass('editable-userpicker')) {
                                        $eEditable.hide();

                                        var options = item.options.options ? item.options.options : [];
                                        var userName = item.strings.anonymous ? item.strings.anonymous : '';
                                        var valueField = item.options.valueField ? item.options.valueField : 'value';
                                        var labelField = item.options.labelField ? item.options.labelField : 'label';

                                        $.each(options, function (key, value) {
                                            if (value[valueField] == newValue) {
                                                userName = value[labelField];
                                            }
                                        });

                                        setTimeout(function () {
                                            $eEditable.html(userName).show();
                                            $.fn.editable.defaults.success = function (response, newValue) {
                                            }
                                        }, 300);
                                    }
                                };

                                setTimeout(function () {
                                    // After inline editing popup opened, run behaviors to initialize selectize.js.
                                    e107.attachBehaviors();
                                }, 300);
                            });
                        });
                    }
                });
            }
        }
    };

    /**
     * Behavior to initialize autocomplete fields with selectize.js
     *
     * @type {{attach: e107.behaviors.selectizeInit.attach}}
     */
    e107.behaviors.selectizeInit = {
        attach: function (context, settings) {
            if (e107.settings.selectize) {
                $.each(e107.settings.selectize, function (index, item) {
                    $(context).find('#' + item.id).once('selectize-init').each(function () {
                        var $item = $(this);

                        $item.selectize({
                            // General options.
                            items: item.options.items ? item.options.items : [],
                            delimiter: item.options.delimiter ? item.options.delimiter : ',',
                            diacritics: item.options.diacritics ? item.options.diacritics : false,
                            create: item.options.create ? item.options.create : false,
                            createOnBlur: item.options.createOnBlur ? item.options.createOnBlur : false,
                            highlight: item.options.highlight ? item.options.highlight : false,
                            persist: item.options.persist ? item.options.persist : false,
                            openOnFocus: item.options.openOnFocus ? item.options.openOnFocus : false,
                            maxOptions: item.options.maxOptions ? item.options.maxOptions : null,
                            maxItems: item.options.maxItems ? item.options.maxItems : null,
                            hideSelected: item.options.hideSelected ? item.options.hideSelected : false,
                            closeAfterSelect: item.options.closeAfterSelect ? item.options.closeAfterSelect : false,
                            allowEmptyOption: item.options.allowEmptyOption ? item.options.allowEmptyOption : false,
                            scrollDuration: item.options.scrollDuration ? item.options.scrollDuration : 60,
                            loadThrottle: item.options.loadThrottle ? item.options.loadThrottle : 300,
                            loadingClass: item.options.loadingClass ? item.options.loadingClass : 'loading',
                            preload: item.options.preload ? item.options.preload : false,
                            dropdownParent: item.options.dropdownParent ? item.options.dropdownParent : null,
                            addPrecedence: item.options.addPrecedence ? item.options.addPrecedence : false,
                            selectOnTab: item.options.selectOnTab ? item.options.selectOnTab : false,
                            mode: item.options.mode ? item.options.mode : 'multi',
                            plugins: item.options.plugins ? item.options.plugins : [],

                            // Data / Searching.
                            options: item.options.options ? item.options.options : [],
                            valueField: item.options.valueField ? item.options.valueField : 'value',
                            labelField: item.options.labelField ? item.options.labelField : 'label',
                            searchField: item.options.searchField ? item.options.searchField : 'label',

                            wrapperClass: item.options.wrapperClass || 'selectize-control',
                            inputClass: item.options.inputClass || 'selectize-input',
                            dropdownClass: item.options.dropdownClass || 'selectize-dropdown',
                            dropdownContentClass: item.options.dropdownContentClass || 'selectize-dropdown-content',
                            copyClassesToDropdown: item.options.copyClassesToDropdown ? item.options.copyClassesToDropdown : null,

                            // Callbacks.
                            load: function (query, callback) {
                                var loadPath = item.options.loadPath ? item.options.loadPath : '';
                                if (loadPath == '') return callback([]);
                                if (!query.length) return callback([]);

                                $.ajax({
                                    url: loadPath,
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        q: query,
                                        l: item.options.maxOptions ? item.options.maxOptions : 10
                                    },
                                    error: function () {
                                        callback([]);
                                    },
                                    success: function (data) {
                                        // Update items in options array of this field.
                                        e107.settings.selectize[index].options.options = data;
                                        callback(data);
                                    }
                                });
                            }
                        });
                    });

                });
            }
        }
    };

})(jQuery);
