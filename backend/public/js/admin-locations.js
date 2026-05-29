(function () {
  const OTHER_CITY = '__other__';

  function csrfToken() {
    return (
      document.querySelector('meta[name="csrf-token"]')?.content ||
      document.querySelector('input[name="_token"]')?.value ||
      ''
    );
  }

  function initBlock(root) {
    if (root.dataset.locInitialized === '1') return;
    root.dataset.locInitialized = '1';

    const data = window.PS_LOCATIONS;
    if (!data) return;

    const countrySelect = root.querySelector('[data-loc-country]');
    const provinceSelect = root.querySelector('[data-loc-province]');
    const citySelect = root.querySelector('[data-loc-city]');
    const customCityInput = root.querySelector('[data-loc-custom-city]');
    const customCityWrap = root.querySelector('[data-loc-custom-wrap]');
    const geocodeBtn = root.querySelector('[data-loc-geocode]');
    const latInput = root.querySelector('[data-loc-lat]');
    const lngInput = root.querySelector('[data-loc-lng]');
    const mapHost = document.getElementById(root.dataset.mapId);

    const initialCountry = root.dataset.initialCountry || '';
    const initialProvince = root.dataset.initialProvince || '';
    const initialCity = root.dataset.initialCity || '';

    function fillProvinces(preserveProvince) {
      const country = countrySelect.value;
      const prev = preserveProvince ? provinceSelect.value : '';
      provinceSelect.innerHTML = '<option value="">— Seleccionar —</option>';
      citySelect.innerHTML = '<option value="">— Seleccionar —</option>';
      citySelect.disabled = true;

      const list = data.provinces[country] || [];
      list.forEach(function (p) {
        const opt = document.createElement('option');
        opt.value = p;
        opt.textContent = p;
        provinceSelect.appendChild(opt);
      });
      provinceSelect.disabled = !list.length;

      if (preserveProvince && prev && list.includes(prev)) {
        provinceSelect.value = prev;
      }
    }

    function fillCities(preserveCity) {
      const province = provinceSelect.value;
      const prev = preserveCity ? citySelect.value : '';
      citySelect.innerHTML = '<option value="">— Seleccionar —</option>';

      const list = data.cities[province] || [];
      list.forEach(function (c) {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        citySelect.appendChild(opt);
      });

      const other = document.createElement('option');
      other.value = OTHER_CITY;
      other.textContent = '— Otra (escribir) —';
      citySelect.appendChild(other);

      citySelect.disabled = !province;

      if (preserveCity) {
        if (prev && prev !== OTHER_CITY && list.includes(prev)) {
          citySelect.value = prev;
        } else if (initialCity && !list.includes(initialCity) && prev === OTHER_CITY) {
          citySelect.value = OTHER_CITY;
        }
      }
      toggleCustomCity();
    }

    function toggleCustomCity() {
      const show = citySelect.value === OTHER_CITY;
      if (customCityWrap) customCityWrap.hidden = !show;
    }

    function resolveCity() {
      if (citySelect.value === OTHER_CITY) {
        return (customCityInput?.value || '').trim();
      }
      return (citySelect.value || '').trim();
    }

    function applyInitialCity() {
      if (!initialCity) return;
      const list = data.cities[provinceSelect.value] || [];
      if (list.includes(initialCity)) {
        citySelect.value = initialCity;
      } else {
        citySelect.value = OTHER_CITY;
        if (customCityInput) customCityInput.value = initialCity;
      }
      toggleCustomCity();
    }

    function loadInitialSelection() {
      if (!initialCountry) return;
      countrySelect.value = initialCountry;
      fillProvinces(true);
      if (!initialProvince) return;
      provinceSelect.value = initialProvince;
      fillCities(false);
      applyInitialCity();
    }

    countrySelect?.addEventListener('change', function () {
      fillProvinces(false);
      fillCities(false);
      if (customCityInput) customCityInput.value = '';
    });

    provinceSelect?.addEventListener('change', function () {
      fillCities(false);
      if (customCityInput) customCityInput.value = '';
    });

    citySelect?.addEventListener('change', toggleCustomCity);

    loadInitialSelection();

    geocodeBtn?.addEventListener('click', async function () {
      const city = resolveCity();
      const province = provinceSelect.value;
      const country = countrySelect.value;
      if (!city || !province || !country) {
        alert('Selecciona país, provincia y ciudad (o escribe una personalizada).');
        return;
      }

      geocodeBtn.disabled = true;
      const label = geocodeBtn.innerHTML;
      geocodeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      try {
        const res = await fetch(window.PS_GEOCODE_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ city: city, province: province, country: country }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'No se encontraron coordenadas.');

        latInput.value = json.data.latitude;
        lngInput.value = json.data.longitude;
        if (mapHost?._adminMapApi) {
          mapHost._adminMapApi.setMarker(json.data.latitude, json.data.longitude, 14);
        }
      } catch (err) {
        alert(err.message || 'Error al geocodificar.');
      } finally {
        geocodeBtn.disabled = false;
        geocodeBtn.innerHTML = label;
      }
    });

    const form = root.closest('form');
    form?.addEventListener('submit', function () {
      if (citySelect.value !== OTHER_CITY) return;

      const custom = (customCityInput?.value || '').trim();
      if (!custom) return;

      let hidden = form.querySelector('[data-loc-city-resolved]');
      if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = citySelect.name;
        hidden.setAttribute('data-loc-city-resolved', '1');
        form.appendChild(hidden);
      }
      hidden.value = custom;
      citySelect.removeAttribute('name');
    });
  }

  function initAll() {
    document.querySelectorAll('[data-admin-location]').forEach(initBlock);
    document.dispatchEvent(new CustomEvent('admin:maps-init'));
  }

  document.addEventListener('DOMContentLoaded', initAll);
  document.addEventListener('admin:page-loaded', function () {
    document.querySelectorAll('[data-admin-location]').forEach(function (root) {
      root.dataset.locInitialized = '0';
    });
    initAll();
  });
})();
