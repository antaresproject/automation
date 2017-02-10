$(document).ready(function () {
    $('.automation-select-category', document).on('change', function (e) {
        var table = $(this).closest('.tbl-c').find('[data-table-init]');
        if (table.length < 0) {
            return false;
        }
        var api = table.dataTable().api();
        var val = $.fn.dataTable.util.escapeRegex($(this).val());
        api.column(2).search(val, false, false).draw();
    });
});