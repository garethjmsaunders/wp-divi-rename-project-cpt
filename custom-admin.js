jQuery(document).ready(function($) {
    $('#menu_icon_dropdown').each(function() {
        var select = $(this);
        select.find('option').each(function() {
            var iconClass = $(this).data('icon');
            if (iconClass) {
                $(this).addClass(iconClass);
            }
        });

        select.on('change', function() {
            var selectedOption = $(this).find('option:selected');
            select.attr('class', '').addClass(selectedOption.data('icon'));
        }).trigger('change');
    });
});