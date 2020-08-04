require([ 'jquery'], function($) {
    $(document).ready(function() {
        $('body').on('submit', '#currency-converter-form', function (e) {
            var frm = $(this);
            var data = frm.serialize() + '&format=json';
            e.preventDefault();
            $.ajax({
                type: frm.attr('method'),
                url: frm.attr('action'),
                data: data,
                dataType: "json",
                showLoader: true,
                success: function (data) {
                    if (true === data.success) {
                        $('#currency-to').val(data.converted_value);
                        $('#currency-to-field').css('display', 'block');
                        $('.errors').html('');
                    } else {
                        $('#currency-to').val('');
                        $('#currency-to-field').css('display', 'none');
                        $('.errors').html(data.error_message);
                    }
                },
                error: function (data) {
                    $('#currency-to').val('');
                    $('#currency-to-field').css('display', 'none');
                },
            });
        });
    });
});
