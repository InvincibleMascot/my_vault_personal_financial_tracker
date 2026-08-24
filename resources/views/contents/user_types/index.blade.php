<x-app-layout>
    <link href="{{ asset('assets/css/dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dataTable.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="row p-2 align-items-center">
                    <div class="col-xl-6">
                        <h4>USER TYPES</h4>
                    </div>
                    <div class="col-xl-6 text-end">
                        <button type="button" class="btn btn-primary" id="add_user_type_btn">
                            +&nbsp;ADD&nbsp;USER&nbsp;TYPE
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div id="user_types_alert"></div>

                    <div class="table-responsive">
                        <table id="user_types_table" class="table table-striped table-hover align-middle w-100" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>USER TYPE</th>
                                    <th>ACCESS TO</th>
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

    <div class="modal fade" id="userTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="user_type_form" class="modal-content">
                @csrf
                <input type="hidden" name="user_type_id" id="user_type_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="userTypeModalTitle">Add User Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="user_type">User Type</label>
                        <select name="user_type" id="user_type" class="form-select" required>
                            <option value="">Select User Type</option>
                            @foreach (\App\Models\UserType::labels() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="user_type_error"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="save_user_type_btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.bootstrap5.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            var userTypeModal = new bootstrap.Modal(document.getElementById('userTypeModal'));

            function safeText(value) {
                if (value === null || value === undefined || value === '') {
                    return '-';
                }

                return $('<div>').text(value).html();
            }

            function showUserTypesAlert(type, message) {
                var cls = type === 'success' ? 'alert-success' : 'alert-danger';
                var html = '<div class="alert ' + cls + ' alert-dismissible fade show" role="alert">' + safeText(message) + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';

                $('#user_types_alert').html(html);

                setTimeout(function () {
                    $('#user_types_alert').find('.alert').fadeOut(400, function () {
                        $(this).remove();
                    });
                }, 3000);
            }

            function resetForm() {
                $('#user_type_form')[0].reset();
                $('#user_type_id').val('');
                $('#user_type').removeClass('is-invalid');
                $('#user_type_error').text('');
                $('#userTypeModalTitle').text('Add User Type');
                $('#save_user_type_btn').prop('disabled', false).text('Save');
            }

            var table = $('#user_types_table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollX: true,
                order: [[0, 'asc']],
                ajax: {
                    url: "{{ route('user-types.list-table') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        showUserTypesAlert('error', 'Listing failed. Check controller, route, or database columns.');
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'user_type_label', name: 'user_type_label', render: safeText },
                    { data: 'access_to_label', name: 'access_to_label', render: safeText },
                    { data: 'created_at', name: 'created_at', render: safeText },
                    { data: 'updated_at', name: 'updated_at', render: safeText },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return `
                                <div class="account-actions">
                                    <button type="button" class="action-btn edit edit_user_type" rec_id="${btoa(row.id)}">Edit</button>
                                    <button type="button" class="action-btn delete delete_user_type" rec_id="${btoa(row.id)}">Delete</button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            $('#add_user_type_btn').on('click', function () {
                resetForm();
                userTypeModal.show();
            });

            $(document).on('click', '.edit_user_type', function () {
                resetForm();

                var id = atob($(this).attr('rec_id'));

                $.ajax({
                    url: "{{ url('/user-types/edit') }}/" + id,
                    type: 'GET',
                    success: function (res) {
                        $('#userTypeModalTitle').text('Edit User Type');
                        $('#user_type_id').val(btoa(res.data.id));
                        $('#user_type').val(res.data.user_type);
                        userTypeModal.show();
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        showUserTypesAlert('error', xhr.responseJSON?.message || 'Unable to load user type.');
                    }
                });
            });

            $('#user_type_form').on('submit', function (event) {
                event.preventDefault();

                $('#user_type').removeClass('is-invalid');
                $('#user_type_error').text('');
                $('#save_user_type_btn').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: "{{ route('user-types.submit') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        userTypeModal.hide();
                        table.ajax.reload(null, false);
                        showUserTypesAlert('success', res.message || 'User type saved successfully.');
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON?.errors || {};

                        if (errors.user_type) {
                            $('#user_type').addClass('is-invalid');
                            $('#user_type_error').text(errors.user_type[0]);
                        }

                        showUserTypesAlert('error', xhr.responseJSON?.message || 'Unable to save user type.');
                    },
                    complete: function () {
                        $('#save_user_type_btn').prop('disabled', false).text('Save');
                    }
                });
            });

            $(document).on('click', '.delete_user_type', function () {
                if (!confirm('Are you sure you want to delete this user type?')) return;

                var id = atob($(this).attr('rec_id'));

                $.ajax({
                    url: "{{ url('/user-types/delete') }}/" + id,
                    type: 'GET',
                    success: function (res) {
                        table.ajax.reload(null, false);
                        showUserTypesAlert('success', res.message || 'User type deleted successfully.');
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        showUserTypesAlert('error', xhr.responseJSON?.message || 'Unable to delete user type.');
                    }
                });
            });
        });
    </script>
</x-app-layout>