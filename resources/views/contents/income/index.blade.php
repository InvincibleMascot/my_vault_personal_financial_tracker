<x-app-layout>
    <link href="{{ asset('assets/css/dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dataTable.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="row p-2">
                    <div class="col-xl-6">
                        <h4>INCOMES</h4>
                    </div>
                    <div class="col-xl-6 text-end">
                        <a href="{{ url('/income_add') }}" class="btn btn-primary">
                            +&nbsp;ADD&nbsp;INCOME
                        </a>
                    </div>
                </div>

                <div class="income-table-card">
                    <div class="card-body">
                        <div id="income_alert"></div>

                        <div class="table-responsive accounts-table-wrap">
                            <table id="incomeTable" class="table table-striped table-hover align-middle w-100 income_table" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>INCOME NAME</th>
                                        <th>INCOME TYPE</th>
                                        <th>INCOME DESCRIPTION</th>
                                        <th>INCOME FROM</th>
                                        <th>INCOME AMOUNT</th>
                                        <th>INCOME CREDITED ACCOUNT NUMBER</th>
                                        <th>INCOME Duration</th>
                                        <th>CREATED AT</th>
                                        <th>UPDATED AT</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>

    <script>
        var incomeTable;
            $(document).ready(function () {

                incomeTable = $('#incomeTable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    scrollX: true,
                    order: [[0, 'desc']],
                    ajax: {
                        url: "{{ route('income_list_table') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        error: function (xhr) {
                            console.log(xhr.responseText);
                            showIncomeAlert('error', 'Listing failed. Check controller, route, or database columns.');
                        }
                    },
                    language: {
                        processing: `
                            <div class="accounts-loader">
                                <div class="accounts-loader-icon"></div>
                                <div>
                                    <div class="accounts-loader-title">Loading Income</div>
                                    <div class="accounts-loader-sub">Fetching latest records</div>
                                </div>
                            </div>
                        `
                    },
                    columns: [
        { data: 'id', name: 'id' },
        {
            data: 'income_name',
            name: 'income_name',
            render: function (data) {
                return safeText(data);
            }
        },
        {
            data: 'income_type',
            name: 'income_type',
            render: function (data) {
                return safeText(data);
            }
        },
        {
            data: 'income_description',
            name: 'income_description',
            render: function (data) {
                return safeText(data);
            }
        },
        {
            data: 'income_from',
            name: 'income_from',
            render: function (data) {
                return safeText(data);
            }
        },
        {
            data: 'income_amount',
            name: 'income_amount',
            render: function (data) {
                return formatMoney(data);
            }
        },
        {
            data: 'income_credited_account_number',
            name: 'income_credited_account_number',
            render: function (data) {
                return safeText(data);
            }
        },
        {
            data: 'income_duration',
            name: 'income_duration',
            render: function (data) {
                return safeText(data);
            }
        },
        {
            data: 'created_at',
            name: 'created_at',
            render: function (data) {
                return safeText(data);
            }
        },
        {
            data: 'updated_at',
            name: 'updated_at',
            render: function (data) {
                return safeText(data);
            }
        },
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
                    <div class="account-actions">
                        <a href="{{ url('/income_view') }}/${row.id}" class="action-btn view view_income">View</a>
                        <a href="{{ url('/income_edit') }}/${row.id}" class="action-btn edit edit_income">Edit</a>
                        <button type="button" class="action-btn delete delete_income" rec_id="${btoa(row.id)}">Delete</button>
                    </div>
                        `;
                    }
                }
            ]
            });
            function showIncomeAlert(type, message) {
                var cls = type === 'success' ? 'alert-success-box' : 'alert-error-box';
                var html = '<div class="alert-box ' + cls + '">' + message + '</div>';

                $('#income_alert').html(html);

                setTimeout(function () {
                    $('#income_alert').find('.alert-box').fadeOut(400, function () {
                        $(this).remove();
                    });
                }, 3000);
            }

            function safeText(data) {
                if (data === null || data === undefined || data === '') {
                    return '-';
                }

                return $('<div>').text(data).html();
            }

            function formatMoney(data) {
                if (data === null || data === undefined || data === '') {
                    return '-';
                }

                return parseFloat(data).toFixed(2);
            }


        });

    </script>

</x-app-layout>