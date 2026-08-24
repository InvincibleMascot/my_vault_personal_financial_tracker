<x-app-layout>
    <link href="{{ asset('assets/css/dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dataTable.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="row p-2 align-items-center">
                    <div class="col-xl-6">
                        <h4>USERS</h4>
                    </div>
                    <div class="col-xl-6 text-end">
                        @if (auth()->user()?->isSuperAdmin())
                            <a href="{{ route('users.add') }}" class="btn btn-primary">
                                +&nbsp;ADD&nbsp;USER
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div id="users_alert"></div>

                    <div class="table-responsive">
                        <table id="users_table" class="table table-striped table-hover align-middle w-100" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NAME</th>
                                    <th>EMAIL</th>
                                    <th>USER TYPE</th>
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

    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.bootstrap5.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            function safeText(value) {
                if (value === null || value === undefined || value === '') {
                    return '-';
                }

                return $('<div>').text(value).html();
            }

            function showUsersAlert(type, message) {
                var cls = type === 'success' ? 'alert-success' : 'alert-danger';
                var html = '<div class="alert ' + cls + ' alert-dismissible fade show" role="alert">' + safeText(message) + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';

                $('#users_alert').html(html);

                setTimeout(function () {
                    $('#users_alert').find('.alert').fadeOut(400, function () {
                        $(this).remove();
                    });
                }, 3000);
            }

            var usersTable = $('#users_table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollX: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route('users.list-table') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        showUsersAlert('error', 'Listing failed. Check controller, route, or database columns.');
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name', render: safeText },
                    { data: 'email', name: 'email', render: safeText },
                    { data: 'user_type_label', name: 'user_type_label', render: safeText },
                    { data: 'created_at', name: 'created_at', render: safeText },
                    { data: 'updated_at', name: 'updated_at', render: safeText },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return `
                                <div class="account-actions">
                                    <a href="{{ url('/users_view') }}/${row.id}" class="action-btn view">View</a>
                                    <a href="{{ url('/users_edit') }}/${row.id}" class="action-btn edit">Edit</a>
                                    <button type="button" class="action-btn delete delete_user" rec_id="${btoa(row.id)}">Delete</button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            $(document).on('click', '.delete_user', function () {
                if (!confirm('Are you sure you want to delete this user?')) return;

                var id = atob($(this).attr('rec_id'));

                $.ajax({
                    url: "{{ url('/users_delete') }}/" + id,
                    type: 'GET',
                    success: function (res) {
                        usersTable.ajax.reload(null, false);
                        showUsersAlert('success', res.message || 'User deleted successfully.');
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        showUsersAlert('error', xhr.responseJSON?.message || 'Unable to delete user.');
                    }
                });
            });
        });
    </script>
</x-app-layout>
