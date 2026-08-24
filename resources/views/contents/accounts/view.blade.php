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
                        <h4>VIEW ACCOUNT</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div id="accounts_alert"></div>

                    <div class="row">
                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" class="form-control" value="{{ $record->account_number ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Account Type</label>
                            <input type="text" class="form-control" value="{{ $record->account_type ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" class="form-control" value="{{ $record->bank_name ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Branch</label>
                            <input type="text" class="form-control" value="{{ $record->branch ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" value="{{ $record->ifsc_code ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Balance</label>
                            <input type="text" class="form-control" value="₹ {{ isset($record->balance) ? number_format($record->balance, 0, '.', ',') : '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Created At</label>
                            <input type="text" class="form-control" value="{{ isset($record->created_at) ? date('d-m-Y H:i', strtotime($record->created_at)) : '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Updated At</label>
                            <input type="text" class="form-control" value="{{ isset($record->updated_at) ? date('d-m-Y H:i', strtotime($record->updated_at)) : '-' }}" readonly>
                        </div>
                        <div class="col-xl-12 text-end mt-3">
                            <a href="{{ url('/accounts') }}" class="btn btn-secondary">
                                Back
                            </a>
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
        });
    </script>
</x-app-layout>