<x-app-layout>
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="row p-2 align-items-center">
                    <div class="col-xl-6">
                        <h4>VIEW USER</h4>
                    </div>
                    <div class="col-xl-6 text-end">
                        <a href="{{ route('users.index') }}" class="btn btn-light">Back</a>
                        <a href="{{ url('/users_edit/' . $record->id) }}" class="btn btn-primary">Edit</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle w-100">
                            <tbody>
                                <tr>
                                    <th style="width: 220px;">ID</th>
                                    <td>{{ $record->id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $record->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $record->email }}</td>
                                </tr>
                                <tr>
                                    <th>User Type</th>
                                    <td>{{ ucwords(str_replace('_', ' ', $record->user_type)) }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $record->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $record->updated_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>