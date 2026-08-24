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
                        <h4>VIEW INCOME</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div id="income_alert"></div>

                    <div class="row">
                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Income Name</label>
                            <input type="text" class="form-control" value="{{ $record->income_name ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Income Type</label>
                            <input type="text" class="form-control" value="{{ $record->income_type ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Income Description</label>
                            <input type="text" class="form-control" value="{{ $record->income_description ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Income From</label>
                            <input type="text" class="form-control" value="{{ $record->income_from ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Income Amount</label>
                            <input type="text" class="form-control"value="{{ isset($record->income_amount) ? '₹ ' . number_format($record->income_amount, 2, '.', ',') : '-' }}"readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Income Credited Account Number</label>
                            <input type="text" class="form-control" value="{{ $record->income_credited_account_number ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Income Duration</label>
                            <input type="text" class="form-control" value="{{ $record->income_duration ?? '-' }}" readonly>
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
                            <a href="{{ url('/income') }}" class="btn btn-secondary">
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
        });
    </script>
</x-app-layout>