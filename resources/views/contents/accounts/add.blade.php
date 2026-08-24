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
                        <h4>ADD ACCOUNT</h4>
                    </div>
                    
                </div>

                <div class="card-body">
                    <div id="accounts_alert"></div>

                    <form id="accounts_add_form">
                        @csrf

                        <div class="row">
                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" name="account_number" id="account_number" class="form-control" maxlength="100">
                                <span class="text-danger error-text account_number_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Account Type <span class="text-danger">*</span></label>
                                <select name="account_type_id" id="account_type_id" class="form-control select2">
                                    <option value="" disabled>Select Account Type</option>
                                    @foreach($account_type as $type)
                                        <option value="{{ $type->id }}">{{ $type->account_type }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-text account_type_id_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control" maxlength="100">
                                <span class="text-danger error-text bank_name_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Branch <span class="text-danger">*</span></label>
                                <input type="text" name="branch" id="branch" class="form-control" maxlength="100">
                                <span class="text-danger error-text branch_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                <input type="text" name="ifsc_code" id="ifsc_code" class="form-control" maxlength="100">
                                <span class="text-danger error-text ifsc_code_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Balance <span class="text-danger">*</span></label>
                                <input type="number" name="balance" id="balance" class="form-control" min="0" step="0.01">
                                <span class="text-danger error-text balance_error"></span>
                            </div>

                            <div class="col-xl-12 text-end mt-3">
                                <a href="{{ url('/accounts') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" id="accounts_submit_btn" class="btn btn-primary">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>

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
            $('.select2').select2({
                width: '100%'
            });

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

            function clearValidationErrors() {
                $('.error-text').text('');
                $('.form-control').removeClass('is-invalid');
            }

            function showValidationErrors(errors) {
                $.each(errors, function (key, value) {
                    $('.' + key + '_error').text(value[0]);
                    $('[name="' + key + '"]').addClass('is-invalid');
                });
            }

            $('#accounts_add_form').on('submit', function (e) {
                e.preventDefault();

                clearValidationErrors();

                $('#accounts_submit_btn').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: "{{ route('accounts_submit') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        showAccountsAlert('success', res.message || 'Account added successfully.');

                        $('#accounts_add_form')[0].reset();
                        $('#account_type_id').val('').trigger('change');

                        setTimeout(function () {
                            window.location.href = "{{ url('/accounts') }}";
                        });
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            showValidationErrors(xhr.responseJSON.errors);
                            showAccountsAlert('error', 'Please check the required fields.');
                        } else {
                            showAccountsAlert('error', xhr.responseJSON?.message || 'Unable to save account.');
                        }
                    },
                    complete: function () {
                        $('#accounts_submit_btn').prop('disabled', false).text('Save');
                    }
                });
            });
        });
    </script>
</x-app-layout>