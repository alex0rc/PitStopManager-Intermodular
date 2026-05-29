@extends('admin.layouts.app')
@php $isEdit = $payment->exists; @endphp
@section('title', $isEdit ? 'Editar pago' : 'Nuevo pago')
@section('page-title', $isEdit ? 'Editar pago' : 'Nuevo pago')

@section('content')
<div class="row"><div class="col-lg-7">
    <div class="card card-admin"><div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('admin.payments.update', $payment) : route('admin.payments.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">Suscripción *</label>
                <select name="subscription_id" id="subscription_id" class="form-select" required>
                    @foreach ($subscriptions as $sub)
                        <option value="{{ $sub->id }}"
                            data-user-id="{{ $sub->user_id }}"
                            data-amount="{{ $sub->plan?->price ?? 0 }}"
                            @selected(old('subscription_id', $payment->subscription_id) == $sub->id)>
                            #{{ $sub->id }} — {{ $sub->user?->name }} — {{ $sub->plan?->name }} ({{ $sub->status }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Usuario *</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(old('user_id', $payment->user_id) == $u->id)>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Importe *</label>
                    <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control"
                        value="{{ old('amount', $payment->amount) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Moneda *</label>
                    <input type="text" name="currency" class="form-control" maxlength="3"
                        value="{{ old('currency', $payment->currency ?? 'EUR') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado *</label>
                    <select name="status" class="form-select" required>
                        @foreach (['pending','succeeded','failed','refunded'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $payment->status) === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Fecha de pago</label>
                <input type="datetime-local" name="paid_at" class="form-control"
                    value="{{ old('paid_at', $payment->paid_at?->format('Y-m-d\TH:i')) }}">
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div></div>
</div></div>
@endsection

@push('scripts')
<script>
(function () {
  const sub = document.getElementById('subscription_id');
  const user = document.getElementById('user_id');
  const amount = document.getElementById('amount');
  if (!sub) return;

  function syncFromSubscription() {
    const opt = sub.selectedOptions[0];
    if (!opt) return;
    if (user && opt.dataset.userId) user.value = opt.dataset.userId;
    if (amount && opt.dataset.amount && !amount.value) amount.value = opt.dataset.amount;
  }

  sub.addEventListener('change', syncFromSubscription);
  syncFromSubscription();
})();
</script>
@endpush
