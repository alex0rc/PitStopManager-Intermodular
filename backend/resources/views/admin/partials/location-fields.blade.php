@php
    $prefix = $prefix ?? '';
    $showAddress = $showAddress ?? false;
    $addressRequired = $addressRequired ?? false;
    $sectionTitle = $sectionTitle ?? 'Ubicación';
    $sectionHint = $sectionHint ?? null;
    $values = $values ?? [];
    $mapId = $mapId ?? ('map-' . uniqid());
    $countries = config('spain_locations.countries', ['España']);

    $field = fn (string $key) => $prefix . $key;
    $val = fn (string $key, $default = '') => $values[$key] ?? $default;
@endphp

<div
    class="admin-location-block"
    data-admin-location
    data-map-id="{{ $mapId }}"
    data-initial-country="{{ $val('country') }}"
    data-initial-province="{{ $val('province') }}"
    data-initial-city="{{ $val('city') }}"
>
    <div class="mb-3">
        <h6 class="fw-bold mb-1">{{ $sectionTitle }}</h6>
        @if ($sectionHint)
            <p class="form-text mb-0">{{ $sectionHint }}</p>
        @endif
    </div>

    @if ($showAddress)
        <div class="mb-3">
            <label class="form-label">Ubicación / dirección @if($addressRequired)*@endif</label>
            <input
                type="text"
                name="location"
                class="form-control"
                value="{{ $val('location') }}"
                placeholder="Ej. Polígono industrial, km 3"
                @if($addressRequired) required @endif
            >
        </div>
    @endif

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">País</label>
            <select name="{{ $field('country') }}" class="form-select" data-loc-country>
                <option value="">— Seleccionar —</option>
                @foreach ($countries as $c)
                    <option value="{{ $c }}" @selected($val('country') === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Provincia</label>
            <select name="{{ $field('province') }}" class="form-select" data-loc-province disabled>
                <option value="">— Seleccionar —</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Ciudad / localidad</label>
            <select name="{{ $field('city') }}" class="form-select" data-loc-city disabled>
                <option value="">— Seleccionar —</option>
            </select>
        </div>
        <div class="col-md-6 mb-3" data-loc-custom-wrap hidden>
            <label class="form-label">Nombre de ciudad</label>
            <input type="text" class="form-control" data-loc-custom-city placeholder="Ej. Zuera, Olvera, Portimão…">
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <span class="fw-semibold small">Coordenadas (mapa / clima)</span>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-loc-geocode>
            <i class="bi bi-search"></i> Centrar por ciudad
        </button>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Latitud</label>
            <input type="number" step="any" name="{{ $field('latitude') }}" class="form-control" data-loc-lat value="{{ $val('latitude') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Longitud</label>
            <input type="number" step="any" name="{{ $field('longitude') }}" class="form-control" data-loc-lng value="{{ $val('longitude') }}">
        </div>
    </div>

    <div
        id="{{ $mapId }}"
        class="admin-map-picker"
        data-admin-map
    ></div>
    <p class="form-text mt-2 mb-0">Haz clic en el mapa o arrastra el marcador para ajustar las coordenadas.</p>
</div>
