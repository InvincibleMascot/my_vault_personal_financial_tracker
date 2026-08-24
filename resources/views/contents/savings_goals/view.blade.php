<x-app-layout>
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="row p-2">
                    <div class="col-xl-6">
                        <h4>VIEW SAVINGS GOAL</h4>
                    </div>

                    <div class="col-xl-6 text-end">
                        <a href="{{ url('/savings_goals') }}" class="btn btn-secondary">Back</a>
                        <a href="{{ url('/savings_edit') }}/{{ $record->id }}" class="btn btn-primary">Edit</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Savings Name</label>
                            <input type="text" class="form-control" value="{{ $record->saving_name ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Savings Category</label>
                            <input type="text" class="form-control" value="{{ $record->savings_category ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Savings For</label>
                            <input type="text" class="form-control" value="{{ $record->savings_for ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Savings Method</label>
                            <input type="text" class="form-control" value="{{ $record->savings_method ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Savings Account</label>
                            <input type="text" class="form-control" value="{{ $record->savings_account ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Savings Duration</label>
                            <input type="text" class="form-control" value="{{ $record->savings_duration ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Savings Amount</label>
                            <input type="text" class="form-control" value="{{ $record->savings_amount !== null ? number_format((float) $record->savings_amount, 2) : '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Total Account Savings</label>
                            <input type="text" class="form-control" value="{{ $record->total_account_savings !== '-' ? number_format((float) $record->total_account_savings, 2) : '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Total Cash Saving</label>
                            <input type="text" class="form-control" value="{{ $record->total_cash_saving !== '-' ? number_format((float) $record->total_cash_saving, 2) : '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Created At</label>
                            <input type="text" class="form-control" value="{{ $record->created_at ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Updated At</label>
                            <input type="text" class="form-control" value="{{ $record->updated_at ?? '-' }}" readonly>
                        </div>

                        <div class="col-xl-3 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="3" readonly>{{ $record->description ?? '-' }}</textarea>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>