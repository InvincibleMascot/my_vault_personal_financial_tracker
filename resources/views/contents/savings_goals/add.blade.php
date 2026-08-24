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
                        <h4>ADD SAVINGS GOAL</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div id="savings_alert"></div>

                    <form id="savings_add_form">
                        @csrf

                        <div class="row">
                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Savings Name <span class="text-danger">*</span></label>
                                <input type="text" name="saving_name" id="saving_name" class="form-control" maxlength="100">
                                <span class="text-danger error-text saving_name_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Savings Category <span class="text-danger">*</span></label>
                                <input type="text" name="savings_category" id="savings_category" class="form-control" maxlength="255">
                                <span class="text-danger error-text savings_category_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Savings For <span class="text-danger">*</span></label>
                                <input type="text" name="savings_for" id="savings_for" class="form-control" maxlength="100">
                                <span class="text-danger error-text savings_for_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Savings Method <span class="text-danger">*</span></label>
                                <select name="savings_method" id="savings_method" class="form-control select2">
                                    <option value="" disabled selected>Select Savings Method</option>

                                    @foreach($transaction_types as $method)
                                        <option value="{{ $method->id }}" data-method="{{ strtolower($method->transaction_type) }}">
                                            {{ $method->transaction_type }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-text savings_method_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3 d-none" id="account_field_box">
                                <label class="form-label">Savings Account <span class="text-danger">*</span></label>

                                <select name="accounts_id" id="accounts_id" class="form-control select2" disabled>
                                    <option value="" disabled selected>Select Account Number</option>

                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->account_number }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="text-danger error-text accounts_id_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Saving Amount <span class="text-danger">*</span></label>
                                <input type="number" name="savings_amount" id="savings_amount" class="form-control" min="0" step="1">
                                <span class="text-danger error-text savings_amount_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Savings Duration <span class="text-danger">*</span></label>

                                <select name="duration" id="duration" class="form-control select2">
                                    <option value="" disabled selected>Select Duration</option>

                                    @foreach($durations as $duration)
                                        <option value="{{ $duration->id }}">
                                            {{ $duration->duration_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="text-danger error-text duration_error"></span>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                                <span class="text-danger error-text description_error"></span>
                            </div>

                            <div class="col-xl-12 text-end mt-3">
                                <a href="{{ url('/savings_goals') }}" class="btn btn-secondary">Cancel</a>

                                <button type="submit" id="savings_submit_btn" class="btn btn-primary">
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

            toggleSavingMethodFields();

            $('#savings_method').on('change', function () {
                toggleSavingMethodFields();
            });

            function toggleSavingMethodFields() {
                var method = $('#savings_method option:selected').data('method');

                method = method ? method.toString().toLowerCase().trim() : '';

                $('#account_field_box').addClass('d-none');
                $('#accounts_id').prop('disabled', true).val('').trigger('change');

                if (method === '') {
                    return;
                }

                if (method !== 'cash') {
                    $('#account_field_box').removeClass('d-none');
                    $('#accounts_id').prop('disabled', false);
                }
            }

            function showSavingsAlert(type, message) {
                var cls = type === 'success' ? 'alert-success-box' : 'alert-error-box';
                var html = '<div class="alert-box ' + cls + '">' + message + '</div>';

                $('#savings_alert').html(html);

                if (type === 'success') {
                    setTimeout(function () {
                        window.location.href = "{{ url('/savings_goals') }}";
                    }, 1000);
                } else {
                    setTimeout(function () {
                        $('#savings_alert').find('.alert-box').fadeOut(400, function () {
                            $(this).remove();
                        });
                    }, 3000);
                }
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

            $('#savings_add_form').on('submit', function (e) {
                e.preventDefault();

                clearValidationErrors();

                $('#savings_submit_btn').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: "{{ route('savings_submit') }}",
                    type: "POST",
                    data: $(this).serialize(),

                    success: function (res) {
                        showSavingsAlert('success', res.message || 'Savings goal added successfully.');

                        $('#savings_add_form')[0].reset();

                        $('#savings_method').val('').trigger('change');
                        $('#accounts_id').val('').trigger('change');
                        $('#duration').val('').trigger('change');

                        toggleSavingMethodFields();
                    },

                    error: function (xhr) {
                        console.log(xhr.responseText);

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            showValidationErrors(xhr.responseJSON.errors);
                            showSavingsAlert('error', 'Please check the required fields.');
                        } else {
                            showSavingsAlert('error', xhr.responseJSON?.message || 'Unable to save savings goal.');
                        }
                    },

                    complete: function () {
                        $('#savings_submit_btn').prop('disabled', false).text('Save');
                    }
                });
            });
        });
    </script>
</x-app-layout>