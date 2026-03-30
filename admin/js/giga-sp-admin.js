(function($) {
    'use strict';
    $(document).ready(function() {
        $('#giga-sp-validate-content').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            $btn.prop('disabled', true).text('Validating...');
            
            // Mocking AJAX call
            setTimeout(function() {
                $btn.prop('disabled', false).text('Validate Schema');
                alert('Schema mapping successfully validated against Google Structured Data rules (Mock).');
            }, 1500);
        });
    });
})(jQuery);
