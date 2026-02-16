<style>
    .card-st {
        margin-bottom: 24px;
        border-radius: 10px;
        border: 1px solid #e9ebec;
        transition: all .3s ease;
        background: linear-gradient(#fff, #fff);
        box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    .mx-md .m-border {
        border-bottom: 1px solid #e9ebec;
        padding: 10px!important;
    }

    .date-range-picker {
        background: #f4f6f8;
        cursor: pointer;
        padding: 8px 15px;
        border: 1px solid #d6d8db;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        font-family: "Shopify Sans", Arial, sans-serif;
        font-size: 14px;
        color: #212b36;
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
        transition: background 0.3s, border-color 0.3s;
    }

    .date-range-picker:hover {
        background: #eaeef2;
        border-color: #c1c7cd;
    }

    .date-range-picker i {
        color: #637381;
        margin-right: 8px;
    }

    .date-range-picker span {
        flex-grow: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .date-range-picker i.fa-caret-down {
        color: #637381;
        margin-left: 8px;
    }

    .hiauto h2 {
       font-size: 17px !important;
    }
    
    .card.bg-gray {
        border-radius: 7px;
    }
   
    
    .card-header p {
        margin-bottom: 0px !important;
    }
  
    .card-footer, .card-header {
        padding: 0.78rem 1.5rem !important;
    }
    
    .overall_card_container {
        background: #f6f6f6;
    }
    
    .overall_card .card-header.m-border{
        background: #b2b2b2;
    }
 
</style>

<section id="overall-analytics" class="mob-margin mx-stats m-doctor">
    
    

    <div class="row">
        <div class="col-md-3">
            <a class="w-100" href="#">
                <div class="card-st hiauto" style="">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="text-bold-700 mb-0">1</h2>
                            <p>Total Customers <br><small>(0 to 31 Days)</small></p>
                        </div>
                        <div class="avatar bg-rgba-primary p-50 m-0">
                            <div class="avatar-content"><i class="fa fa-user text-primary font-medium-5"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="w-100" href="#">
                <div class="card-st hiauto" style="">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="text-bold-700 mb-0">2</h2>
                            <p>Total Customers <br><small>(32 to 62 Days)</small></p>
                        </div>
                        <div class="avatar bg-rgba-primary p-50 m-0">
                            <div class="avatar-content"><i class="fa fa-user text-primary font-medium-5"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="w-100" href="#">
                <div class="card-st hiauto" style="">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="text-bold-700 mb-0">4</h2>
                            <p>Total Customers <br><small>(63 to 94 Days)</small></p>
                        </div>
                        <div class="avatar bg-rgba-primary p-50 m-0">
                            <div class="avatar-content"><i class="fa fa-user text-primary font-medium-5"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="w-100" href="#">
                <div class="card-st hiauto" style="">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="text-bold-700 mb-0">12</h2>
                            <p>Total Customers <br><small>(No records)</small></p>
                        </div>
                        <div class="avatar bg-rgba-primary p-50 m-0">
                            <div class="avatar-content"><i class="fa fa-user text-primary font-medium-5"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
    </div>
</section>

<link rel="stylesheet" type="text/css" href="<?= base_url();?>assets/css/placeholder-loading.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
    
    //Date range picker
    $('.datepicker_report').daterangepicker({
        autoUpdateInput: false,
        autoApply: false,
        //autoclose: true, 
        locale: {
            format: 'DD-MM-YYYY', 
            cancelLabel: 'Clear'
        },  
        maxDate: moment().add(0, 'days'), // 30 days from the current day
    })
    
    $('.datepicker_report').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
    });
    
   $(function () {
        // Initialize all elements with class "date-range-picker"
        $('.date-range-picker').each(function () {
            let $this = $(this); // Reference to the current element
    
            let start = moment();
            let end = moment();
    
            // Initialize the Date Range Picker for this element
            $this.daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, function (start, end) {
                // Callback to update the specific element's span
                let dateRange = start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD');
                $this.find('span').html(dateRange);
            });
    
            // Bind to the `apply.daterangepicker` event for this element
            $this.on('apply.daterangepicker', function (ev, picker) {
                let dateRange = picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD');
                let cardName = $this.data('card-name'); // Use a data attribute to identify the related card dynamically
                get_dashboard_stats(dateRange, cardName);
            });
    
            // Initialize the display
            let initialDateRange = start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD');
            $this.find('span').html(initialDateRange);
        });
    
        // AJAX function
        function get_dashboard_stats(date_range, card_name) {
            $("#" + card_name).html('<div class="row"><?php for($i = 0;$i < 5;$i++):?><div class="col-md-6 col-sm-3 col-xs-12"><div class="ph-item card-st border-radius-2"><div class="ph-col-12"><div class="ph-row"><div class="ph-col-6 big"></div><div class="ph-col-6 empty"></div><div class="ph-col-4"></div><div class="ph-col-8 empty"></div><div class="ph-col-12"></div><div class="ph-col-10"></div></div></div></div></div><?php endfor;?></div>');
            
            let data = { 
                date_range: date_range,
                card_name: card_name,
            };
            
            setTimeout(function () {
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('inventory/get_ajax_dashboard_data')?>",
                    data: data,
                    success: function (htmlData) {
                        $("#" + card_name).html(htmlData);
                    }
                });
            }, 1000);
        }
        
    });

</script>