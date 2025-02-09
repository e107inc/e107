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
                $item.on("change keyup", function () {
                  var $this = $(this);
                  var useUnix = $this.attr("data-date-unix");
                  var newValue = $this.val();

                  if (useUnix !== "true" || newValue == "") {
                    var id = $this.attr("id");
                    var newTarget = "#" + id.replace("e-datepicker-", "");
                    $(newTarget).val(newValue);
                  }

                  // If UNIX timestamp is required, manually convert the date string (dd.mm.yyyy)
                  if (useUnix === "true") {
                    var parts = newValue.split(".");
                    if (parts.length === 3) {
                      var day = parseInt(parts[0], 10);
                      var month = parseInt(parts[1], 10) - 1; // Month is zero-based in JS
                      var year = parseInt(parts[2], 10);

                      // Validate date parts
                      if (!isNaN(day) && !isNaN(month) && !isNaN(year)) {
                        var date = new Date(year, month, day);
                        var unixTimestamp = Math.floor(date.getTime() / 1000);
                        var id = $this.attr("id");
                        var newTarget = "#" + id.replace("e-datepicker-", "");
                        $(newTarget).val(unixTimestamp); // Update hidden UNIX field
                      }
                    }
                  } else {
                    // If not using UNIX timestamp, update with raw value
                    var id = $this.attr("id");
                    var newTarget = "#" + id.replace("e-datepicker-", "");
                    $(newTarget).val(newValue); // Update hidden field with text input value
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
