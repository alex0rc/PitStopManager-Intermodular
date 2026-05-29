@extends('admin.layouts.app')
@php $isEdit = $subscription->exists; @endphp
@section('title', $isEdit ? 'Editar suscripción' : 'Nueva suscripción')
@section('page-title', $isEdit ? 'Editar suscripción' : 'Nueva suscripción')

@section('content')
<div class="row"><div class="col-lg-7">
    <div class="card card-admin"><div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('admin.subscriptions.update', $subscription) : route('admin.subscriptions.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">Usuario *</label>
                <select name="user_id" class="form-select" required>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(old('user_id', $subscription->user_id) == $u->id)>
                            {{ $u->name }} ({{ $u->email }}) — {{ $u->role }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Plan *</label>
                <select name="plan_id" id="plan_id" class="form-select" required>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}"
                            data-days="{{ $plan->duration_days }}"
                            data-price="{{ $plan->price }}"
                            @selected(old('plan_id', $subscription->plan_id) == $plan->id)>
                            {{ $plan->name }} — {{ number_format($plan->price, 2) }} € / {{ $plan->duration_days }} días
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Estado *</label>
                <select name="status" class="form-select" required>
                    @foreach (['pending','active','expired','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $subscription->status) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                <div class="form-text">Si marcas <strong>active</strong> y el usuario es piloto, se promociona a organizador.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Inicio *</label>
                    <input type="date" name="starts_at" id="starts_at" class="form-control"
                        value="{{ old('starts_at', $subscription->starts_at?->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fin *</label>
                    <input type="date" name="ends_at" id="ends_at" class="form-control"
                        value="{{ old('ends_at', $subscription->ends_at?->format('Y-m-d')) }}" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div></div>
</div></div>
@endsection

@push('scripts')
<script>
(function () {
  const plan = document.getElementById('plan_id');
  const starts = document.getElementById('starts_at');
  const ends = document.getElementById('ends_at');
  if (!plan || !starts || !ends) return;

  plan.addEventListener('change', function () {
    const opt = plan.selectedOptions[0];
    const days = parseInt(opt?.dataset.days || '30', 10);
    if (!starts.value) starts.value = new Date().toISOString().slice(0, 10);
    const d = new Date(starts.value);
    d.setDate(d.getDate() + days);
    ends.value = d.toISOString().slice(0, 10);
  });
})();
</script>
@endpush
