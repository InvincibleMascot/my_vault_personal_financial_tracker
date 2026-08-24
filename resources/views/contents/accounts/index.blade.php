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
                        <h4>ACCOUNTS</h4>
                    </div>
                    <div class="col-xl-6 text-end">
                        <a href="{{ url('/accounts_add') }}" class="btn btn-primary">
                            +&nbsp;ADD&nbsp;ACCOUNT
                        </a>
                    </div>
                    </div>


                <div class="row g-2 accounts-summary-row">

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="summary-card summary-card-bank">
                        <div class="summary-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-label">Number of Accounts</div>
                            <div class="summary-value" id="summary_accounts_count">
                                {{ number_format($accountsCount) }}
                            </div>
                            <div class="summary-sub">Total bank accounts added</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="summary-card summary-card-available">
                        <div class="summary-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-label">Total Account Balance</div>
                            <div class="summary-value" id="summary_accounts_balance">
                                ₹ {{ number_format($totalAccountsBalance) }}
                            </div>
                            <div class="summary-sub">Total balance in all accounts</div>
                        </div>
                    </div>
                </div>

            </div>


                <div class="accounts-table-card">
                    <div class="card-body">
                        <div id="accounts_alert"></div>

                        <div class="table-responsive accounts-table-wrap">
                        <table id="accounts_table" class="table table-striped table-hover align-middle w-100 accounts-table" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ACCOUNT NUMBER</th>
                                    <th>ACCOUNT TYPE</th>
                                    <th>BANK NAME</th>
                                    <th>BRANCH</th>
                                    <th>IFSC CODE</th>
                                    <th>BALANCE</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>      
                                    <th>Actions</th>
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
$(document).ready(function () {
    var accountsTable;

    function safeText(value) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }

        return $('<div>').text(value).html();
    }

    function formatMoney(value) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }

        value = parseInt(value || 0);

        return '₹ ' + value.toLocaleString('en-IN');
    }

    function showAccountsAlert(type, message) {
        var cls = type === 'success' ? 'alert-success-box' : 'alert-error-box';
        var html = '<div class="alert-box ' + cls + '">' + message + '</div>';

        $('#accounts_alert').html(html);

        setTimeout(function () {
            $('#accounts_alert').find('.alert-box').fadeOut(400, function () {
                $(this).remove();
            });
        }, 3000);
    }

    accountsTable = $('#accounts_table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: true,
        order: [[0, 'desc']],
        ajax: {
            url: "{{ route('accounts_list_table') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                showAccountsAlert('error', 'Listing failed. Check controller, route, or database columns.');
            }
        },
        language: {
            processing: `
                <div class="accounts-loader">
                    <div class="accounts-loader-icon"></div>
                    <div>
                        <div class="accounts-loader-title">Loading Accounts</div>
                        <div class="accounts-loader-sub">Fetching latest records</div>
                    </div>
                </div>
            `
        },
        columns: [
            { data: 'id', name: 'id' },
            {
                data: 'account_number',
                name: 'account_number',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'account_type',
                name: 'account_type',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'bank_name',
                name: 'bank_name',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'branch',
                name: 'branch',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'ifsc_code',
                name: 'ifsc_code',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'balance',
                name: 'balance',
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
                            <a href="{{ url('/accounts_view') }}/${row.id}" class="action-btn view view_account">View</a>
                            <a href="{{ url('/accounts_edit') }}/${row.id}" class="action-btn edit edit_account">Edit</a>
                            <button type="button" class="action-btn delete delete_account" rec_id="${btoa(row.id)}">Delete</button>
                        </div>
                    `;
                }
            }
        ]
    });

    function adjustAccountsTable() {
        if ($.fn.DataTable.isDataTable('#accounts_table')) {
            accountsTable.columns.adjust();
        }
    }

    setTimeout(adjustAccountsTable, 200);
    setTimeout(adjustAccountsTable, 600);

    $(window).on('resize', function () {
        setTimeout(adjustAccountsTable, 250);
    });

    $(document).on('click', '.sidemenu-toggle, .app-sidebar__toggle, .sidebar-toggle, [data-bs-toggle="sidebar"], [data-toggle="sidebar"]', function () {
        setTimeout(adjustAccountsTable, 250);
        setTimeout(adjustAccountsTable, 600);
    });

    if (window.ResizeObserver) {
        var tableWrap = document.querySelector('.accounts-table-wrap');

        if (tableWrap) {
            var resizeObserver = new ResizeObserver(function () {
                adjustAccountsTable();
            });

            resizeObserver.observe(tableWrap);
        }
    }

    $(document).on('click', '.delete_account', function () {
        if (!confirm('Are you sure you want to delete this Account?')) return;

        var id = atob($(this).attr('rec_id'));

        $.ajax({
            url: "{{ url('/accounts_delete') }}/" + id,
            type: "GET",
            success: function (res) {
                accountsTable.ajax.reload(null, false);
                showAccountsAlert('success', res.message || 'Account deleted successfully.');
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                showAccountsAlert('error', xhr.responseJSON?.message || 'Unable to delete account.');
            }
        });
    });
});


</script>


    
</x-app-layout>