<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
    $('.datepicker_report').daterangepicker({
        autoUpdateInput: false,
        autoApply: true,
        locale: { format: 'DD-MM-YYYY' },
        maxDate: moment().add(0, 'days'),
    });

    $('.datepicker_loading_report').daterangepicker({
        autoUpdateInput: false,
        autoApply: true,
        locale: { format: 'DD-MM-YYYY' }
    });

    $('.datepicker_expected_report').daterangepicker({
        autoUpdateInput: false,
        autoApply: true,
        locale: { format: 'DD-MM-YYYY' }
    });

    $('.datepicker_report, .datepicker_loading_report, .datepicker_expected_report').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        if (typeof dataTable !== 'undefined') {
            dataTable.draw();
        }
    });

    $('.datepicker_report, .datepicker_loading_report, .datepicker_expected_report').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        if (typeof dataTable !== 'undefined') {
            dataTable.draw();
        }
    });

    $(document).on('keyup input', '#filter_keywords', function() {
        if (typeof dataTable !== 'undefined') {
            dataTable.draw();
        }
    });

    $(document).on('change', '#filter_type, #filter_status', function() {
        if (typeof dataTable !== 'undefined') {
            dataTable.draw();
        }
    });

    $(document).on('click', '#btn_reset_filter', function() {
        $('#filter_date_range').val('');
        $('#filter_loading_date_range').val('');
        $('#filter_expected_date_range').val('');
        $('#filter_keywords').val('');
        $('#filter_type').val('');
        if ($('#filter_status').length) {
            var defaultStatus = $('#filter_status').data('default') || '';
            $('#filter_status').val(defaultStatus);
        }
        if (typeof dataTable !== 'undefined') {
            dataTable.draw();
        }
    });
</script>
