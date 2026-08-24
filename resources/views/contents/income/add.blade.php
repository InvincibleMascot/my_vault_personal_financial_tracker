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
                        <h4>ADD INCOME</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div id="income_alert"></div>

                    <form id="income_add_form">
                        @csrf

                        <div class="row">
                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Income Name <span class="text-danger">*</span></label>
                                <input type="text" name="income_name" id="income_name" class="form-control" maxlength="100">
                                <span class="text-danger error-text income_name_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">
                                    Income Type <span class="text-danger">*</span>
                                </label>

                                <select name="income_type" id="income_type" class="form-control select2">
                                    <option value="" disabled selected>Select Type</option>

                                    @foreach($income_type as $type)
                                        <option value="{{ $type->id }}">
                                            {{ $type->income_type_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="text-danger error-text income_type_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Income From <span class="text-danger">*</span></label>
                                <input type="text" name="income_from" id="income_from" class="form-control" maxlength="100">
                                <span class="text-danger error-text income_from_error"></span>
                            </div>


                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Income Amount <span class="text-danger">*</span></label>
                                <input type="number" name="income_amount" id="income_amount" class="form-control" min="0" step="0.01">
                                <span class="text-danger error-text income_amount_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Income Credited Account Number <span class="text-danger">*</span></label>
                                <select name="income_credited_account_number" id="income_credited_account_number" class="form-control select2">
                                    <option value="" disabled selected>Select Account Number</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->account_number }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-text income_credited_account_number_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">
                                    Income Period <span class="text-danger">*</span>
                                </label>

                                <select name="income_duration" id="income_duration" class="form-control select2">
                                    <option value="" disabled selected>Select Type</option>

                                    @foreach($income_duration as $duration)
                                        <option value="{{ $duration->id }}">
                                            {{ $duration->income_duration }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="text-danger error-text income_duration_error"></span>
                            </div>

                            <div class="col-xl-6 mb-3">
                                <label class="form-label">Income Description <span class="text-danger">*</span></label>
                                <textarea name="income_description" id="income_description" class="form-control" rows="3"></textarea>
                                <span class="text-danger error-text income_description_error"></span>
                            </div>

                            <div class="col-xl-12 text-end mt-3">
                                <a href="{{ url('/income') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" id="income_submit_btn" class="btn btn-primary income_submit">
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

            function showIncomeAlert(type, message) {
                var cls = type === 'success' ? 'alert-success-box' : 'alert-error-box';
                var html = '<div class="alert-box ' + cls + '">' + message + '</div>';

                $('#income_alert').html(html);

                setTimeout(function () {
                    window.location.href = "{{ url('/income') }}";
                }, 1000);
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
            $('#income_add_form').on('submit', function (e) {
                e.preventDefault();

                clearValidationErrors();

                $('#income_submit_btn').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: "{{ route('income_submit') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        showIncomeAlert('success', res.message || 'Income added successfully.');

                        $('#income_add_form')[0].reset();

                        $('#income_type').val('').trigger('change');
                        $('#income_credited_account_number').val('').trigger('change');

                        setTimeout(function () {
                            window.location.href = "{{ url('/income') }}";
                        }, 1000);
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            showValidationErrors(xhr.responseJSON.errors);
                            showIncomeAlert('error', 'Please check the required fields.');
                        } else {
                            showIncomeAlert('error', xhr.responseJSON?.message || 'Unable to save income.');
                        }
                    },
                    complete: function () {
                        $('#income_submit_btn').prop('disabled', false).text('Save');
                    }
                });
            });
            success: function (res) {
                showIncomeAlert('success', res.message || 'Income added successfully.');

                $('#income_add_form')[0].reset();

                $('#income_type').val('').trigger('change');
                $('#income_duration').val('').trigger('change');
                $('#income_credited_account_number').val('').trigger('change');

                setTimeout(function () {
                    window.location.href = "{{ url('/income') }}";
                }, 1000);
            },
        });
    </script>
</x-app-layout>