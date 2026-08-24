<x-app-layout>
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="row p-2 align-items-center">
                    <div class="col-xl-6">
                        <h4>EDIT USER</h4>
                    </div>
                    <div class="col-xl-6 text-end">
                        <a href="{{ route('users.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>

                <div class="card-body">
                    <div id="users_alert"></div>

                    <form id="user_form">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ base64_encode($record->id) }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ $record->name }}" required>
                                <div class="invalid-feedback" id="name_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{ $record->email }}" required>
                                <div class="invalid-feedback" id="email_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="user_type_id">User Type</label>
                                <select name="user_type_id" id="user_type_id" class="form-select" required>
                                    <option value="">Select User Type</option>
                                    @foreach ($userTypes as $userType)
                                        <option value="{{ $userType->id }}" @selected((int) $record->user_type_id === (int) $userType->id)>
                                            {{ ucwords(str_replace('_', ' ', $userType->user_type)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="user_type_id_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" name="password" id="password" class="form-control">
                                <div class="invalid-feedback" id="password_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="password_confirmation">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary" id="save_user_btn">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            function safeText(value) {
                return $('<div>').text(value || '').html();
            }

            function showUsersAlert(type, message) {
                var cls = type === 'success' ? 'alert-success' : 'alert-danger';
                $('#users_alert').html('<div class="alert ' + cls + '">' + safeText(message) + '</div>');
            }

            $('#user_form').on('submit', function (event) {
                event.preventDefault();

                $('.form-control, .form-select').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#save_user_btn').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: "{{ route('users.submit') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        showUsersAlert('success', res.message || 'User updated successfully.');
                        setTimeout(function () {
                            window.location.href = "{{ route('users.index') }}";
                        }, 700);
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON?.errors || {};

                        Object.keys(errors).forEach(function (field) {
                            $('#' + field).addClass('is-invalid');
                            $('#' + field + '_error').text(errors[field][0]);
                        });

                        showUsersAlert('error', xhr.responseJSON?.message || 'Unable to save user.');
                    },
                    complete: function () {
                        $('#save_user_btn').prop('disabled', false).text('Update');
                    }
                });
            });
        });
    </script>
</x-app-layout>