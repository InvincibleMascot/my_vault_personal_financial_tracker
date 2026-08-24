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
                    <h4>TRANSACTIONS</h4>
                </div>
    </div>

    <div class="row g-3 mb-4 transaction-summary-row">

    <div class="col-xl-3 col-md-6">
        <div class="summary-card summary-card-bank">
            <div class="summary-icon">
                <i class="bi bi-bank"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">Account Balance</div>
                <div class="summary-value" id="summary_account_balance">
                    ₹ {{ number_format($accountBalance) }}
                </div>
                <div class="summary-sub">Total money in all bank accounts</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="summary-card summary-card-cash">
            <div class="summary-icon">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">Cash Balance</div>
                <div class="summary-value" id="summary_cash_balance">
                    ₹ {{ number_format($cashBalance) }}
                </div>
                <div class="summary-sub">Available physical cash</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="summary-card summary-card-spent">
            <div class="summary-icon">
                <i class="bi bi-arrow-down-circle"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">Spent This Month</div>
                <div class="summary-value" id="summary_total_spent">
                    ₹ {{ number_format($totalSpentThisMonth) }}
                </div>
                <div class="summary-sub">Total debit transactions this month</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="summary-card summary-card-available">
            <div class="summary-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">Available Balance</div>
                <div class="summary-value" id="summary_available_balance">
                    ₹ {{ number_format($availableBalance) }}
                </div>
                <div class="summary-sub">Bank balance + cash balance</div>
            </div>
        </div>
    </div>

    <div class="col-xl-15 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
            +&nbsp;ADD&nbsp;TRANSACTION
        </button>
    </div>

</div>

    <div class="card transaction-table-card">
        <div class="card-body">
            <div id="transaction_alert"></div>
            

            <div class="table-responsive transaction-table-wrap">
            <table id="transactions_table" class="table table-striped table-hover align-middle w-100 transaction-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Transaction Name</th>
                        <th>Transaction Description</th>
                        <th>Transaction Method</th>
                        <th>Transaction Type</th>
                        <th>Transaction Category</th>
                        <th>Amount</th>
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

    <!-- Add Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <form id="addTransactionForm">
                    @csrf

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Transaction</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Transaction Name <span class="text-danger">*</span></label>
                                <input type="text" name="transaction_name" class="form-control" placeholder="Enter transaction name" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Transaction Method <span class="text-danger">*</span></label>
                                <select name="transaction_method_id" id="transaction_method_id" class="form-control transaction-select2" data-placeholder="Select Transaction Method" required>
                                    <option value="" disabled selected>Select</option>
                                    @foreach($transaction_method as $tm)
                                        <option value="{{ $tm->id }}">{{ $tm->transaction_method }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Transaction Type <span class="text-danger">*</span></label>
                                <select name="transaction_type_id" id="transaction_type_id" class="form-control transaction-select2" data-placeholder="Select Transaction Type" required>
                                    <option value="" disabled selected>Select</option>
                                    @foreach($transaction_type as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->transaction_type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3" id="add_account_number_box" style="display:none;">
                                <label>Account Number <span class="text-danger">*</span></label>
                                <select name="account_number_id" id="account_number_id" class="form-control transaction-select2" data-placeholder="Select Account Number">
                                    <option value="" disabled selected>Select</option>
                                    @foreach($account_number as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_number }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount_paid" class="form-control" placeholder="Enter Amount Transacted" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Transaction Category <span class="text-danger">*</span></label>
                                <select name="transaction_category_id" id="transaction_category_id" class="form-control transaction-select2" data-placeholder="Select Transaction Category" required>
                                    <option value="" disabled selected>Select</option>
                                    @foreach($transaction_category as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->transaction_category }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Transaction Desciption <span class="text-danger">*</span></label>
                                <textarea name="transaction_description" class="form-control" placeholder="Enter Transaction Description" rows="2" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="add_transaction_submit">Save changes</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <form id="editTransactionForm">
                    @csrf

                    <input type="hidden" name="transaction_id" id="edit_transaction_id">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Transaction</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Transaction Name <span class="text-danger">*</span></label>
                                <input type="text" name="transaction_name" id="edit_transaction_name" class="form-control" placeholder="Enter transaction name" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Transaction Method <span class="text-danger">*</span></label>
                                <select name="transaction_method_id" id="edit_transaction_method_id" class="form-control transaction-select2" data-placeholder="Select Transaction Method" required>
                                    <option value="" disabled>Select</option>
                                    @foreach($transaction_method as $tm)
                                        <option value="{{ $tm->id }}">{{ $tm->transaction_method }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Transaction Type <span class="text-danger">*</span></label>
                                <select name="transaction_type_id" id="edit_transaction_type_id" class="form-control transaction-select2" data-placeholder="Select Transaction Type" required>
                                    <option value="" disabled>Select</option>
                                    @foreach($transaction_type as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->transaction_type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Transaction Category <span class="text-danger">*</span></label>
                                <select name="transaction_category_id" id="edit_transaction_category_id" class="form-control transaction-select2" data-placeholder="Select Transaction Category" required>
                                    <option value="" disabled>Select</option>
                                    @foreach($transaction_category as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->transaction_category }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3" id="edit_account_number_box" style="display:none;">
                                <label>Account Number <span class="text-danger">*</span></label>
                                <select name="account_number_id" id="edit_account_number_id" class="form-control transaction-select2" data-placeholder="Select Account Number">
                                    <option value="" disabled>Select</option>
                                    @foreach($account_number as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_number }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount_paid" id="edit_amount_paid" class="form-control" placeholder="Enter Amount Transacted" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Transaction Desciption <span class="text-danger">*</span></label>
                                <textarea name="transaction_description" id="edit_transaction_description" class="form-control" placeholder="Enter Transaction Description" rows="2" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="edit_transaction_submit">Update</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h1 class="modal-title fs-5">View Transaction</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>Transaction Name</label>
                            <input type="text" id="view_transaction_name" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Transaction Method</label>
                            <input type="text" id="view_transaction_method" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Transaction Type</label>
                            <input type="text" id="view_transaction_type" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Account Number</label>
                            <input type="text" id="view_account_number" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Amount</label>
                            <input type="text" id="view_amount_paid" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Transaction Description</label>
                            <textarea id="view_transaction_description" class="form-control" rows="2" readonly></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>


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
    var transactionsTable;

    $('#exampleModal, #editTransactionModal, #viewTransactionModal').appendTo('body');

    function safeText(value) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }

        return $('<div>').text(value).html();
    }

    function showTransactionAlert(type, message) {
        var cls = type === 'success' ? 'alert-success-box' : 'alert-error-box';
        var html = '<div class="alert-box ' + cls + '">' + message + '</div>';

        $('#transaction_alert').html(html);

        setTimeout(function () {
            $('#transaction_alert').find('.alert-box').fadeOut(400, function () {
                $(this).remove();
            });
        }, 3000);
    }

    function initTransactionSelect2(container) {
        var $container = $(container);
        var $modal = $container.hasClass('modal') ? $container : $container.closest('.modal');

        $container.find('.transaction-select2').each(function () {
            var $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                dropdownParent: $modal.length ? $modal : $(document.body),
                width: '100%',
                placeholder: $select.data('placeholder') || 'Select'
            });
        });
    }

    function toggleAddAccountNumber() {
        var typeText = ($('#transaction_type_id option:selected').text() || '').trim().toLowerCase();

        if (typeText !== '' && typeText !== 'cash') {
            $('#add_account_number_box').show();
            $('#account_number_id').prop('required', true);
        } else {
            $('#add_account_number_box').hide();
            $('#account_number_id').prop('required', false).val('').trigger('change');
        }
    }

    function toggleEditAccountNumber() {
        var typeText = ($('#edit_transaction_type_id option:selected').text() || '').trim().toLowerCase();

        if (typeText !== '' && typeText !== 'cash') {
            $('#edit_account_number_box').show();
            $('#edit_account_number_id').prop('required', true);
        } else {
            $('#edit_account_number_box').hide();
            $('#edit_account_number_id').prop('required', false).val('').trigger('change');
        }
    }

    function formatMoney(value) {
        value = parseInt(value || 0);

        return '₹ ' + value.toLocaleString('en-IN');
    }

    function refreshTransactionSummary() {
        $.ajax({
            url: "{{ route('transactions_summary') }}",
            type: "GET",
            success: function (res) {
                $('#summary_account_balance').text(formatMoney(res.accountBalance));
                $('#summary_cash_balance').text(formatMoney(res.cashBalance));
                $('#summary_total_spent').text(formatMoney(res.totalSpentThisMonth));
                $('#summary_available_balance').text(formatMoney(res.availableBalance));
            },
            error: function (xhr) {
                console.log(xhr.responseText);
            }
        });
    }

    $(document).on('change', '#transaction_type_id', function () {
        toggleAddAccountNumber();
    });

    $(document).on('change', '#edit_transaction_type_id', function () {
        toggleEditAccountNumber();
    });

    $('#exampleModal').on('shown.bs.modal', function () {
        initTransactionSelect2('#exampleModal');
    });

    $('#editTransactionModal').on('shown.bs.modal', function () {
        initTransactionSelect2('#editTransactionModal');
    });

    $(document).on('select2:open', function () {
        setTimeout(function () {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        }, 0);
    });

    transactionsTable = $('#transactions_table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: true,
        order: [[0, 'desc']],
        ajax: {
            url: "{{ route('transactions_list_table') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                showTransactionAlert('error', 'Listing failed. Check controller, route, or database columns.');
            }
        },
        language: {
            processing: `
                <div class="transaction-loader">
                    <div class="transaction-loader-icon"></div>
                    <div>
                        <div class="transaction-loader-title">Loading Transactions</div>
                        <div class="transaction-loader-sub">Fetching latest records</div>
                    </div>
                </div>
            `
        },
        columns: [
            { data: 'id', name: 'id' },
            {
                data: 'transaction_name',
                name: 'transaction_name',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'transaction_description',
                name: 'transaction_description',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'transaction_method',
                name: 'transaction_method',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'transaction_type',
                name: 'transaction_type',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'transaction_category',
                name: 'transaction_category',
                render: function (data) {
                    return safeText(data);
                }
            },
            {
                data: 'amount_paid',
                name: 'amount_paid',
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
                        <div class="transaction-actions">
                            <button type="button" class="action-btn view view_transaction" rec_id="${btoa(row.id)}">View</button>
                            <button type="button" class="action-btn edit edit_transaction" rec_id="${btoa(row.id)}">Edit</button>
                            <button type="button" class="action-btn delete delete_transaction" rec_id="${btoa(row.id)}">Delete</button>
                        </div>
                    `;
                }
            }
        ]
    });

    function adjustTransactionsTable() {
        if ($.fn.DataTable.isDataTable('#transactions_table')) {
            transactionsTable.columns.adjust();
        }
    }

    setTimeout(adjustTransactionsTable, 200);
    setTimeout(adjustTransactionsTable, 600);

    $(window).on('resize', function () {
        setTimeout(adjustTransactionsTable, 250);
    });

    $(document).on('click', '.sidemenu-toggle, .app-sidebar__toggle, .sidebar-toggle, [data-bs-toggle="sidebar"], [data-toggle="sidebar"]', function () {
        setTimeout(adjustTransactionsTable, 250);
        setTimeout(adjustTransactionsTable, 600);
    });

    if (window.ResizeObserver) {
        var tableWrap = document.querySelector('.transaction-table-wrap');

        if (tableWrap) {
            var resizeObserver = new ResizeObserver(function () {
                adjustTransactionsTable();
            });

            resizeObserver.observe(tableWrap);
        }
    }

    $('#add_transaction_submit').on('click', function () {
        var form = $('#addTransactionForm');

        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        $('#add_transaction_submit').prop('disabled', true).text('Saving...');

        $.ajax({
            url: "{{ route('transactions_submit') }}",
            type: "POST",
            data: form.serialize(),
            success: function (res) {
                if (res.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('exampleModal')).hide();

                    form[0].reset();
                    $('#exampleModal').find('.transaction-select2').val('').trigger('change');

                    transactionsTable.ajax.reload(null, false);
                    refreshTransactionSummary();
                    showTransactionAlert('success', res.message);
                } else {
                    showTransactionAlert('error', res.message || 'Unable to save transaction.');
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var firstKey = Object.keys(errors)[0];
                    showTransactionAlert('error', errors[firstKey][0]);
                } else {
                    showTransactionAlert('error', xhr.responseJSON?.message || 'Something went wrong while saving.');
                }
            },
            complete: function () {
                $('#add_transaction_submit').prop('disabled', false).text('Save changes');
            }
        });
    });

    $(document).on('click', '.edit_transaction', function () {
        var id = atob($(this).attr('rec_id'));

        $.ajax({
            url: "{{ url('/transactions_edit') }}/" + id,
            type: "GET",
            success: function (res) {
                var data = res.data || res;

                $('#edit_transaction_id').val(btoa(data.id));
                $('#edit_transaction_name').val(data.transaction_name);
                $('#edit_transaction_description').val(data.transaction_description);
                $('#edit_transaction_category_id').val(data.transaction_category_id || '');
                $('#edit_amount_paid').val(data.amount_paid);
                $('#edit_account_number_id').val(data.account_number_id || '');

                var modalEl = document.getElementById('editTransactionModal');
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

                $(modalEl).one('shown.bs.modal', function () {
                    initTransactionSelect2('#editTransactionModal');

                    $('#edit_transaction_method_id').val(data.transaction_method_id).trigger('change');
                    $('#edit_transaction_type_id').val(data.transaction_type_id).trigger('change');
                    $('#edit_account_number_id').val(data.account_number_id || '').trigger('change');
                    $('#edit_transaction_category_id').val(data.transaction_category_id || '').trigger('change');

                    toggleEditAccountNumber();
                });

                modal.show();
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                showTransactionAlert('error', 'Unable to load Transaction details.');
            }
        });
    });

    $('#edit_transaction_submit').on('click', function () {
        var form = $('#editTransactionForm');

        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        $('#edit_transaction_submit').prop('disabled', true).text('Updating...');

        $.ajax({
            url: "{{ route('transactions_submit') }}",
            type: "POST",
            data: form.serialize(),
            success: function (res) {
                if (res.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('editTransactionModal')).hide();

                    transactionsTable.ajax.reload(null, false);
                    refreshTransactionSummary();
                    showTransactionAlert('success', res.message);
                } else {
                    showTransactionAlert('error', res.message || 'Unable to update transaction.');
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var firstKey = Object.keys(errors)[0];
                    showTransactionAlert('error', errors[firstKey][0]);
                } else {
                    showTransactionAlert('error', xhr.responseJSON?.message || 'Something went wrong while updating.');
                }
            },
            complete: function () {
                $('#edit_transaction_submit').prop('disabled', false).text('Update');
            }
        });
    });

    $(document).on('click', '.view_transaction', function () {
        var id = atob($(this).attr('rec_id'));

        $.ajax({
            url: "{{ url('/transactions_view') }}/" + id,
            type: "GET",
            success: function (res) {
                var data = res.data || res;

                $('#view_transaction_name').val(data.transaction_name || '-');
                $('#view_transaction_description').val(data.transaction_description || '-');
                $('#view_transaction_method').val(data.transaction_method || '-');
                $('#view_transaction_category').val(data.transaction_category || '-');
                $('#view_transaction_type').val(data.transaction_type || '-');
                $('#view_account_number').val(data.account_number || '-');
                $('#view_amount_paid').val(data.amount_paid || '-');

                var modalEl = document.getElementById('viewTransactionModal');
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                showTransactionAlert('error', 'Unable to load Transaction details.');
            }
        });
    });

    $(document).on('click', '.delete_transaction', function () {
        if (!confirm('Are you sure you want to delete this Transaction?')) return;

        var id = atob($(this).attr('rec_id'));

        $.ajax({
            url: "{{ url('/transactions_delete') }}/" + id,
            type: "GET",
            success: function (res) {
                transactionsTable.ajax.reload(null, false);
                refreshTransactionSummary();
                showTransactionAlert('success', res.message || 'Transaction deleted successfully.');
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                showTransactionAlert('error', xhr.responseJSON?.message || 'Unable to delete transaction.');
            }
        });
    });

$('#exampleModal').on('hidden.bs.modal', function () {
    $('#addTransactionForm')[0].reset();
    $('#exampleModal').find('.transaction-select2').val('').trigger('change');
    $('#add_account_number_box').hide();
    $('#account_number_id').prop('required', false);
    $('#add_transaction_submit').prop('disabled', false).text('Save changes');
});

$('#editTransactionModal').on('hidden.bs.modal', function () {
    $('#editTransactionForm')[0].reset();
    $('#editTransactionModal').find('.transaction-select2').val('').trigger('change');
    $('#edit_account_number_box').hide();
    $('#edit_account_number_id').prop('required', false);
    $('#edit_transaction_submit').prop('disabled', false).text('Update');
});
});

</script>

</x-app-layout>