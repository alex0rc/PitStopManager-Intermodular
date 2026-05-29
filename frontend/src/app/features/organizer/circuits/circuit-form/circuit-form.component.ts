import { Component, OnInit, inject } from '@angular/core';
import { ReactiveFormsModule, FormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CircuitService } from '../../../../core/services/circuit.service';
import { LocationService } from '../../../../core/services/location.service';
import { NotificationService } from '../../../../core/services/notification.service';
import { MapPickerComponent, MapCenter } from '../../../../shared/map-picker/map-picker.component';
import { OTHER_CITY_VALUE } from '../../../../core/constants/location.constants';

@Component({
  selector: 'app-circuit-form',
  standalone: true,
  imports: [ReactiveFormsModule, FormsModule, RouterLink, MapPickerComponent],
  templateUrl: './circuit-form.component.html',
  styleUrls: ['./circuit-form.component.scss'],
})
export class CircuitFormComponent implements OnInit {
  form!: FormGroup;
  countries: string[] = [];
  provinces: string[] = [];
  cities: string[] = [];
  readonly otherCityValue = OTHER_CITY_VALUE;
  customCity = '';
  mapCenter: MapCenter | null = null;
  isEdit = false;
  circuitId?: number;
  loading = false;
  loadingData = true;
  geocoding = false;
  error = '';
  imagePreview: string | null = null;
  imageFile: File | null = null;

  private notifications = inject(NotificationService);

  constructor(
    private fb: FormBuilder,
    private circuitService: CircuitService,
    private locationService: LocationService,
    private route: ActivatedRoute,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.form = this.fb.group({
      name: ['', [Validators.required, Validators.maxLength(255)]],
      location: ['', [Validators.required, Validators.maxLength(255)]],
      city: [''],
      province: [''],
      country: [''],
      latitude: [null],
      longitude: [null],
      length_meters: [null, [Validators.min(0)]],
      description: [''],
    });

    this.locationService.getCountries().subscribe({
      next: (list) => (this.countries = list),
    });

    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.isEdit = true;
      this.circuitId = +id;
      this.circuitService.getById(this.circuitId).subscribe({
        next: (circuit) => {
          this.form.patchValue({
            name: circuit.name,
            location: circuit.location,
            province: circuit.province || '',
            country: circuit.country || '',
            latitude: circuit.latitude,
            longitude: circuit.longitude,
            length_meters: circuit.length_meters,
            description: circuit.description || '',
          });
          const savedCity = circuit.city || '';
          if (circuit.country) {
            this.onCountryChange(false, () => {
              if (circuit.province) {
                this.onProvinceChange(false, () => {
                  this.applyCityOnLoad(savedCity);
                });
              } else {
                this.applyCityOnLoad(savedCity);
              }
            });
          } else {
            this.applyCityOnLoad(savedCity);
          }
          if (circuit.latitude != null && circuit.longitude != null) {
            this.mapCenter = {
              latitude: Number(circuit.latitude),
              longitude: Number(circuit.longitude),
              zoom: 14,
            };
          }
          if (circuit.image) {
            this.imagePreview = circuit.image;
          }
          this.loadingData = false;
        },
        error: () => {
          this.error = 'Error al cargar el circuito.';
          this.loadingData = false;
        },
      });
    } else {
      this.loadingData = false;
    }
  }

  get showCustomCity(): boolean {
    return this.form.get('city')?.value === this.otherCityValue;
  }

  private applyCityOnLoad(savedCity: string): void {
    if (!savedCity) return;
    if (this.cities.includes(savedCity)) {
      this.form.patchValue({ city: savedCity });
    } else {
      this.form.patchValue({ city: this.otherCityValue });
      this.customCity = savedCity;
    }
  }

  onCountryChange(reset = true, done?: () => void): void {
    const country = this.form.get('country')?.value;
    this.provinces = [];
    this.cities = [];
    if (reset) {
      this.form.patchValue({ province: '', city: '' });
      this.customCity = '';
    }
    if (country) {
      this.locationService.getProvinces(country).subscribe({
        next: (list) => {
          this.provinces = list;
          done?.();
        },
      });
    } else {
      done?.();
    }
  }

  onProvinceChange(reset = true, done?: () => void): void {
    const province = this.form.get('province')?.value;
    this.cities = [];
    if (reset) {
      this.form.patchValue({ city: '' });
      this.customCity = '';
    }
    if (province) {
      this.locationService.getCities(province).subscribe({
        next: (list) => {
          this.cities = list;
          done?.();
        },
      });
    } else {
      done?.();
    }
  }

  resolveCityName(): string {
    const city = this.form.get('city')?.value;
    if (city === this.otherCityValue) {
      return this.customCity.trim();
    }
    return (city || '').trim();
  }

  fetchCoordinates(): void {
    const city = this.resolveCityName();
    const { province, country } = this.form.value;
    if (!city || !province || !country) {
      this.error = 'Selecciona país, provincia y ciudad (o escribe una personalizada).';
      return;
    }
    this.geocoding = true;
    this.error = '';
    this.locationService.geocode(city, province, country).subscribe({
      next: (coords) => {
        this.form.patchValue({
          latitude: coords.latitude,
          longitude: coords.longitude,
        });
        this.mapCenter = {
          latitude: coords.latitude,
          longitude: coords.longitude,
          zoom: 14,
        };
        this.geocoding = false;
      },
      error: (err) => {
        this.geocoding = false;
        this.error = err?.error?.message || 'No se pudieron obtener las coordenadas.';
      },
    });
  }

  onImageSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    this.imageFile = input.files[0];
    const reader = new FileReader();
    reader.onload = () => (this.imagePreview = reader.result as string);
    reader.readAsDataURL(this.imageFile);
  }

  onMapCoordinates(coords: { latitude: number; longitude: number }): void {
    this.form.patchValue({
      latitude: coords.latitude,
      longitude: coords.longitude,
    });
  }

  onSubmit(): void {
    if (this.showCustomCity && !this.customCity.trim()) {
      this.error = 'Escribe el nombre de la ciudad personalizada.';
      return;
    }

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.loading = true;
    this.error = '';
    const data = { ...this.form.value };
    data.city = this.resolveCityName();

    const request$ = this.isEdit
      ? this.circuitService.update(this.circuitId!, data)
      : this.circuitService.create(data);

    request$.subscribe({
      next: (circuit) => {
        const id = this.circuitId ?? circuit.id;
        if (this.imageFile && id) {
          this.circuitService.uploadImage(id, this.imageFile).subscribe({
            next: () => this.afterSave(!this.isEdit),
            error: () => {
              this.notifications.warning('Circuito guardado, pero falló la subida de la imagen.');
              this.afterSave(!this.isEdit);
            },
          });
        } else {
          this.afterSave(!this.isEdit);
        }
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al guardar el circuito.';
        this.loading = false;
      },
    });
  }

  private afterSave(isNew: boolean): void {
    if (isNew) {
      this.notifications.info(
        'Tu circuito está pendiente de aprobación por un administrador.',
        'Propuesta enviada',
      );
    }
    this.router.navigate(['/organizer/circuits']);
  }

  isInvalid(field: string): boolean {
    const control = this.form.get(field);
    return !!(control && control.invalid && control.touched);
  }
}
