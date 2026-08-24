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
                        <h4>SAVINGS GOALS</h4>
                    </div>

                    <div class="col-xl-6 text-end">
                        <a href="{{ url('/savings_add') }}" class="btn btn-primary">
                            +&nbsp;ADD&nbsp;SAVINGS
                        </a>
                    </div>
                </div>

                <div class="row p-2">
                    <div class="col-xl-4 col-md-4 mb-3">
                        <div class="card custom-card">
                            <div class="card-body">
                                <h6>Total Saving Amount</h6>
                                <h4>{{ number_format($savingTotals->total_saving_amount ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4 mb-3">
                        <div class="card custom-card">
                            <div class="card-body">
                                <h6>Total Account Savings</h6>
                                <h4>{{ number_format($savingTotals->total_account_savings ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4 mb-3">
                        <div class="card custom-card">
                            <div class="card-body">
                                <h6>Total Cash Savings</h6>
                                <h4>{{ number_format($savingTotals->total_cash_saving ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="savings-table-card">
                    <div class="card-body">
                        <div id="savings_alert"></div>

                        <div class="table-responsive accounts-table-wrap">
                            <table id="savingsTable" class="table table-striped table-hover align-middle w-100 savings_table" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>SAVINGS NAME</th>
                                        <th>CATEGORY</th>
                                        <th>DESCRIPTION</th>
                                        <th>SAVINGS FOR</th>
                                        <th>SAVINGS ACCOUNT</th>
                                        <th>SAVINGS METHOD</th>
                                        <th>SAVINGS DURATION</th>
                                        <th>SAVINGS AMOUNT</th>
                                        <th>TOTAL ACCOUNT SAVINGS</th>
                                        <th>TOTAL CASH SAVING</th>
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
        var savingsTable;

        $(document).ready(function () {

            savingsTable = $('#savingsTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollX: true,
                order: [[0, 'desc']],

                ajax: {
                    url: "{{ route('savings_list_table') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        showSavingsAlert('error', 'Listing failed. Check controller, route, or database columns.');
                    }
                },

                language: {
                    processing: `
                        <div class="accounts-loader">
                            <div class="accounts-loader-icon"></div>
                            <div>
                                <div class="accounts-loader-title">Loading Savings Goals</div>
                                <div class="accounts-loader-sub">Fetching latest records</div>
                            </div>
                        </div>
                    `
                },

                columns: [
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'savings_name',
                        name: 'savings_name',
                        render: function (data) {
                            return safeText(data);
                        }
                    },
                    {
                        data: 'savings_category',
                        name: 'savings_category',
                        render: function (data) {
                            return safeText(data);
                        }
                    },
                    {
                        data: 'savings_description',
                        name: 'savings_description',
                        render: function (data) {
                            return safeText(data);
                        }
                    },
                    {
                        data: 'savings_for',
                        name: 'savings_for',
                        render: function (data) {
                            return safeText(data);
                        }
                    },
                    {
                        data: 'savings_account',
                        name: 'savings_account',
                        render: function (data) {
                            return safeText(data);
                        }
                    },
                    {
                        data: 'savings_method',
                        name: 'savings_method',
                        render: function (data) {
                            return safeText(data);
                        }
                    },
                    {
                        data: 'savings_duration',
                        name: 'savings_duration',
                        render: function (data) {
                            return safeText(data);
                        }
                    },
                    {
                        data: 'savings_amount',
                        name: 'savings_amount',
                        render: function (data) {
                            return formatMoney(data);
                        }
                    },
                    {
                        data: 'total_account_savings',
                        name: 'total_account_savings',
                        render: function (data) {
                            return formatMoney(data);
                        }
                    },
                    {
                        data: 'total_cash_saving',
                        name: 'total_cash_saving',
                        render: function (data) {
                            return formatMoney(data);
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
                                    <a href="{{ url('/savings_view') }}/${row.id}" class="action-btn view view_savings">View</a>
                                    <a href="{{ url('/savings_edit') }}/${row.id}" class="action-btn edit edit_savings">Edit</a>
                                    <button type="button" class="action-btn delete delete_savings" rec_id="${btoa(row.id)}">Delete</button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            $(document).on('click', '.delete_savings', function () {
                var rec_id = $(this).attr('rec_id');

                if (!confirm('Are you sure you want to delete this savings goal?')) {
                    return;
                }

                $.ajax({
                    url: "{{ route('savings_delete') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        rec_id: rec_id
                    },

                    success: function (res) {
                        showSavingsAlert('success', res.message || 'Savings goal deleted successfully.');

                        setTimeout(function () {
                            window.location.reload();
                        }, 800);
                    },

                    error: function (xhr) {
                        console.log(xhr.responseText);
                        showSavingsAlert('error', xhr.responseJSON?.message || 'Unable to delete savings goal.');
                    }
                });
            });

            function showSavingsAlert(type, message) {
                var cls = type === 'success' ? 'alert-success-box' : 'alert-error-box';
                var html = '<div class="alert-box ' + cls + '">' + message + '</div>';

                $('#savings_alert').html(html);

                setTimeout(function () {
                    $('#savings_alert').find('.alert-box').fadeOut(400, function () {
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
                if (data === null || data === undefined || data === '' || data === '-') {
                    return '-';
                }

                var amount = parseFloat(data);

                if (isNaN(amount)) {
                    return '-';
                }

                return amount.toFixed(2);
            }

        });
    </script>
</x-app-layout>