// If you don't insert this line into your JS, you may see the error: e107 is not defined.
var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($) {

    /**
     * Behavior to initialize date-time-picker on elements.
     *
     * @type {{attach: e107.behaviors.bootstrapDatetimepickerInit.attach}}
     */
    e107.behaviors.bootstrapDatetimepickerInit = {
        attach: function (context, settings) {
            $(context).find('input.e-date,input.e-datetime').once('datetimepicker-onchange-init').each(function () {
                var $item = $(this);

                // Fix for changeDate() not being fired when value manually altered.
                $item.on("change keyup", function() {
                    var $this = $(this);
                    var useUnix = $this.attr("data-date-unix");
                    var newValue = $this.val();
                    var id = $this.attr("id");
                    var newTarget = "#" + id.replace("e-datepicker-", "");

                    // For non-UNIX fields or empty values, just copy the value
                    if (useUnix !== "true" || newValue === "") {
                        $(newTarget).val(newValue);
                        return;
                    }

                    // For UNIX timestamp conversion, use the datetimepicker instance
                    try {
                        // Get date from the picker (handles format automatically)
                        var pickerInstance = $this.data('datetimepicker');
                        if (!pickerInstance) return;

                        var date = pickerInstance.getDate();
                        if (!date || isNaN(date.getTime())) return;

                        // Convert to UNIX timestamp
                        var unixTimestamp = Math.floor(date.getTime() / 1000);

                        // Apply timezone offset if this is a datetime field
                        if ($this.hasClass('e-datetime')) {
                            var offset = parseInt($this.attr("data-date-timezone-offset"));
                            if (!isNaN(offset)) {
                                var browserOffset = date.getTimezoneOffset() * 60;
                                var relativeOffset = browserOffset + offset;
                                unixTimestamp = unixTimestamp - relativeOffset;
                            }
                        }

                        $(newTarget).val(unixTimestamp);
                    } catch (e) {
                        console.error("Error processing date:", e);
                    }
                });
            });

            $(context).find('input.e-date').once('datetimepicker-init').each(function () {
                var $item = $(this);

                $item.datetimepicker({
                    minView: "month",
                    maxView: "decade",
                    autoclose: true,
                    format: $item.attr("data-date-format"),
                    weekStart: $item.attr("data-date-firstday"),
                    language: $item.attr("data-date-language"),
                }).on("changeDate", function (ev) {
                    var useUnix = $(this).attr("data-date-unix");
                    var newValue = "";
                    var newTarget = "#" + ev.target.id.replace("e-datepicker-", "");

                    if (useUnix === "true") {
                        newValue = parseInt(ev.date.getTime() / 1000);
                    }
                    else {
                        newValue = $("#" + ev.target.id).val();
                    }

                    $(newTarget).val( newValue);
                });
            });

            $(context).find('input.e-datetime').once('datetimepicker-init').each(function () {
                var $item = $(this);

                $item.datetimepicker({
                    autoclose: true,
                    format: $item.attr("data-date-format"),
                    weekStart: $item.attr("data-date-firstday"),
                    showMeridian: $item.attr("data-date-ampm"),
                    language: $item.attr("data-date-language")

                }).on("changeDate", function (ev) {
                    var useUnix = $(this).attr("data-date-unix");
                    var newValue = "";
                    var newTarget = "#" + ev.target.id.replace("e-datepicker-", "");

                    if (useUnix === "true") {
                        newValue = parseInt(ev.date.getTime() / 1000);
                    }
                    else {
                        newValue = $("#" + ev.target.id).val();
                    }

                     offset = parseInt($item.attr("data-date-timezone-offset"));

                     if(offset) // adjust UTC value to target timezone. ie. timezone other than the one of the browser.
                     {
                        browserOffset = ev.date.getTimezoneOffset() * 60;
                        relativeOffset = browserOffset + offset;

                        console.log("Browser Offset: " + browserOffset);
                        console.log('Offset: ' + offset);
                        console.log('Relative Offset: ' + relativeOffset);

                        newValue = newValue - relativeOffset;
                     }

                    $(newTarget).val(newValue);

                })
            });
        }
    };

})(jQuery);
