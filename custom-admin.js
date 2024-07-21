jQuery(document).ready(function($) {
    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var $state = $(
            '<span><span class="dashicons ' + state.id + '"></span> ' + state.text + '</span>'
        );
        return $state;
    }

    $('#menu-icon-select').select2({
        templateResult: formatState,
        templateSelection: formatState,
        width: '100%' // Adjust as needed
    });
});
