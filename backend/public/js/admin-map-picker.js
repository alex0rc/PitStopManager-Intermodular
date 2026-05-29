(function () {
  function destroyMap(host) {
    if (host._adminMapApi) {
      host._adminMapApi.map.remove();
      host._adminMapApi = null;
      host.dataset.mapInitialized = '0';
    }
  }

  function initMap(host) {
    if (host.dataset.mapInitialized === '1' || typeof L === 'undefined') return;

    const block = host.closest('[data-admin-location]');
    const latInput = block?.querySelector('[data-loc-lat]');
    const lngInput = block?.querySelector('[data-loc-lng]');

    const map = L.map(host, { scrollWheelZoom: true }).setView([40.4168, -3.7038], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    let marker = null;

    function round6(n) {
      return Math.round(n * 1_000_000) / 1_000_000;
    }

    function updateInputs(lat, lng) {
      if (latInput) latInput.value = round6(lat);
      if (lngInput) lngInput.value = round6(lng);
    }

    function setMarker(lat, lng, zoom, emit) {
      const icon = L.icon({
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
      });

      if (marker) {
        marker.setLatLng([lat, lng]);
      } else {
        marker = L.marker([lat, lng], { icon: icon, draggable: true }).addTo(map);
        marker.on('dragend', function () {
          const pos = marker.getLatLng();
          updateInputs(pos.lat, pos.lng);
        });
      }

      const z = zoom ?? (map.getZoom() < 10 ? 14 : map.getZoom());
      map.setView([lat, lng], z);
      if (emit !== false) updateInputs(lat, lng);
    }

    map.on('click', function (e) {
      setMarker(e.latlng.lat, e.latlng.lng, null, true);
    });

    host._adminMapApi = { map: map, setMarker: setMarker };
    host.dataset.mapInitialized = '1';

    const lat = parseFloat(latInput?.value);
    const lng = parseFloat(lngInput?.value);
    if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
      setMarker(lat, lng, 14, false);
    }

    setTimeout(function () {
      map.invalidateSize();
    }, 150);
  }

  function initAll() {
    document.querySelectorAll('[data-admin-map]').forEach(initMap);
  }

  function reinitAll() {
    document.querySelectorAll('[data-admin-map]').forEach(function (host) {
      destroyMap(host);
      initMap(host);
    });
  }

  document.addEventListener('DOMContentLoaded', initAll);
  document.addEventListener('admin:page-loaded', reinitAll);
  document.addEventListener('admin:maps-init', reinitAll);
})();
