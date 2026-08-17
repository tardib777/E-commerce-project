@extends('layouts.app')
@section('topbar_title', 'All Transactions')
@section('content')

@php
    $badge = fn($s) => match(strtolower($s ?? '')) {
        'paid', 'finished', 'confirmed' => 'badge-soft-success',
        'waiting', 'confirming', 'pending', 'sending' => 'badge-soft-warning',
        'failed', 'expired', 'refunded' => 'badge-soft-danger',
        default => 'badge-soft-muted',
    };
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="page-title mb-1">All Transactions</h3>
        <p class="page-subtitle">{{ $transactions->count() }} payment transaction{{ $transactions->count() == 1 ? '' : 's' }} recorded.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table dash-table align-middle">
                <thead><tr>
                    <th>#</th><th>Order</th><th>Customer</th><th>Amount</th><th>Method</th><th>Status</th><th>Reference</th><th class="text-end">Date</th>
                </tr></thead>
                <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td class="text-muted">{{ $tx->id }}</td>
                        <td class="fw-semibold">#{{ $tx->order_id }}</td>
                        <td class="text-muted">{{ optional($tx->user)->firstname }} {{ optional($tx->user)->lastname }}</td>
                        <td class="fw-semibold">${{ number_format($tx->amount, 2) }}</td>
                        <td><span class="badge badge-pill badge-soft-info">{{ $tx->method }}</span></td>
                        <td><span class="badge badge-pill {{ $badge($tx->status) }}">{{ ucfirst($tx->status) }}</span></td>
                        <td class="text-muted text-truncate" style="max-width:150px;" title="{{ $tx->transaction_id }}">{{ $tx->transaction_id ?: '—' }}</td>
                        <td class="text-end text-muted">{{ $tx->created_at ? $tx->created_at->format('M d, Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No transactions recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
