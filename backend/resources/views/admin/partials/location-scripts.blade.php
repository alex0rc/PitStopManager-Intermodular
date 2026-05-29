<script>
    window.PS_LOCATIONS = @json(config('spain_locations'));
    window.PS_GEOCODE_URL = @json(route('admin.locations.geocode'));
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="{{ asset('js/admin-map-picker.js') }}" defer></script>
<script src="{{ asset('js/admin-locations.js') }}" defer></script>
