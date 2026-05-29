import { Component, OnInit } from '@angular/core';
import { ReactiveFormsModule, FormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { ChampionshipService } from '../../../../core/services/championship.service';
import { CategoryService } from '../../../../core/services/category.service';
import { SubscriptionService } from '../../../../core/services/subscription.service';
import { LocationService } from '../../../../core/services/location.service';
import { Category } from '../../../../core/models/category.model';
import { SubscriptionQuota } from '../../../../core/models/subscription.model';
import { MapPickerComponent, MapCenter } from '../../../../shared/map-picker/map-picker.component';
import { OTHER_CITY_VALUE } from '../../../../core/constants/location.constants';

@Component({
  selector: 'app-championship-form',
  standalone: true,
  imports: [ReactiveFormsModule, FormsModule, RouterLink, MapPickerComponent],
  templateUrl: './championship-form.component.html',
  styleUrls: ['./championship-form.component.scss'],
})
export class ChampionshipFormComponent implements OnInit {
  form!: FormGroup;
  categories: Category[] = [];
  countries: string[] = [];
  provinces: string[] = [];
  cities: string[] = [];
  readonly otherCityValue = OTHER_CITY_VALUE;
  customVenueCity = '';
  mapCenter: MapCenter | null = null;
  isEdit = false;
  championshipId?: number;
  loading = false;
  loadingData = true;
  geocoding = false;
  error = '';
  quota?: SubscriptionQuota;
  imagePreview: string | null = null;
  imageFile: File | null = null;
  imageUploading = false;

  constructor(
    private fb: FormBuilder,
    private championshipService: ChampionshipService,
    private categoryService: CategoryService,
    private subscriptionService: SubscriptionService,
    private locationService: LocationService,
    private route: ActivatedRoute,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.form = this.fb.group({
      name: ['', [Validators.required, Validators.maxLength(255)]],
      category_id: [null, Validators.required],
      description: [''],
      season_year: [new Date().getFullYear(), [Validators.required, Validators.min(2000), Validators.max(2100)]],
      kart_modality: ['rental', Validators.required],
      engine_class: [''],
      start_date: [''],
      end_date: [''],
      venue_country: [''],
      venue_province: [''],
      venue_city: [''],
      venue_latitude: [null],
      venue_longitude: [null],
    });

    this.locationService.getCountries().subscribe({
      next: (list) => (this.countries = list),
    });

    this.categoryService.getAll().subscribe({
      next: (cats) => {
        this.categories = cats;
        this.checkEditMode();
      },
      error: () => {
        this.error = 'Error al cargar las categorías.';
        this.loadingData = false;
      },
    });
  }

  get showCustomVenueCity(): boolean {
    return this.form.get('venue_city')?.value === this.otherCityValue;
  }

  onCountryChange(reset = true, done?: () => void): void {
    const country = this.form.get('venue_country')?.value;
    this.provinces = [];
    this.cities = [];
    if (reset) {
      this.form.patchValue({ venue_province: '', venue_city: '' });
      this.customVenueCity = '';
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
    const province = this.form.get('venue_province')?.value;
    this.cities = [];
    if (reset) {
      this.form.patchValue({ venue_city: '' });
      this.customVenueCity = '';
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

  private applyVenueCityOnLoad(savedCity: string): void {
    if (!savedCity) return;
    if (this.cities.includes(savedCity)) {
      this.form.patchValue({ venue_city: savedCity });
    } else {
      this.form.patchValue({ venue_city: this.otherCityValue });
      this.customVenueCity = savedCity;
    }
  }

  resolveVenueCityName(): string {
    const city = this.form.get('venue_city')?.value;
    if (city === this.otherCityValue) {
      return this.customVenueCity.trim();
    }
    return (city || '').trim();
  }

  fetchCoordinates(): void {
    const city = this.resolveVenueCityName();
    const { venue_province: province, venue_country: country } = this.form.value;
    if (!city || !province || !country) {
      this.error = 'Selecciona país, provincia y ciudad (o escribe una personalizada).';
      return;
    }
    this.geocoding = true;
    this.error = '';
    this.locationService.geocode(city, province, country).subscribe({
      next: (coords) => {
        this.form.patchValue({
          venue_latitude: coords.latitude,
          venue_longitude: coords.longitude,
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

  onMapCoordinates(coords: { latitude: number; longitude: number }): void {
    this.form.patchValue({
      venue_latitude: coords.latitude,
      venue_longitude: coords.longitude,
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

  private checkEditMode(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.isEdit = true;
      this.championshipId = +id;
      this.championshipService.getById(this.championshipId).subscribe({
        next: (champ) => {
          this.form.patchValue({
            name: champ.name,
            category_id: champ.category_id,
            description: champ.description || '',
            season_year: champ.season_year,
            kart_modality: champ.kart_modality ?? 'rental',
            engine_class: champ.engine_class ?? '',
            start_date: champ.start_date || '',
            end_date: champ.end_date || '',
            venue_country: champ.venue_country || '',
            venue_province: champ.venue_province || '',
            venue_latitude: champ.venue_latitude,
            venue_longitude: champ.venue_longitude,
          });
          const savedCity = champ.venue_city || '';
          if (champ.image) this.imagePreview = champ.image;
          if (champ.venue_country) {
            this.onCountryChange(false, () => {
              if (champ.venue_province) {
                this.onProvinceChange(false, () => this.applyVenueCityOnLoad(savedCity));
              } else {
                this.applyVenueCityOnLoad(savedCity);
              }
            });
          } else {
            this.applyVenueCityOnLoad(savedCity);
          }
          if (champ.venue_latitude != null && champ.venue_longitude != null) {
            this.mapCenter = {
              latitude: Number(champ.venue_latitude),
              longitude: Number(champ.venue_longitude),
              zoom: 14,
            };
          }
          this.loadingData = false;
        },
        error: () => {
          this.error = 'Error al cargar el campeonato.';
          this.loadingData = false;
        },
      });
    } else {
      this.subscriptionService.getMySubscriptionWithQuota().subscribe({
        next: ({ quota }) => {
          this.quota = quota;
          if (!quota.can_create_championship && quota.deny_reason) {
            this.error = quota.deny_reason;
          }
          this.loadingData = false;
        },
        error: () => {
          this.loadingData = false;
        },
      });
    }
  }

  get canSubmit(): boolean {
    return this.isEdit || (this.quota?.can_create_championship ?? true);
  }

  onSubmit(): void {
    if (!this.canSubmit) {
      this.error = this.quota?.deny_reason || 'No puedes crear más campeonatos con tu plan actual.';
      return;
    }

    if (this.showCustomVenueCity && !this.customVenueCity.trim()) {
      this.error = 'Escribe el nombre de la ciudad de la sede.';
      return;
    }

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.loading = true;
    this.error = '';
    const data = { ...this.form.value };
    data.venue_city = this.resolveVenueCityName();

    const request$ = this.isEdit
      ? this.championshipService.update(this.championshipId!, data)
      : this.championshipService.create(data);

    request$.subscribe({
      next: (champ) => {
        const id = this.championshipId ?? champ.id;
        if (this.imageFile && id) {
          this.uploadImageThenNavigate(id);
        } else {
          this.router.navigate(['/organizer/championships']);
        }
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al guardar el campeonato.';
        this.loading = false;
      },
    });
  }

  private uploadImageThenNavigate(id: number): void {
    this.imageUploading = true;
    this.championshipService.uploadImage(id, this.imageFile!).subscribe({
      next: () => {
        this.imageUploading = false;
        this.router.navigate(['/organizer/championships']);
      },
      error: (err) => {
        this.imageUploading = false;
        this.error = err.displayMessage || err.error?.message || 'Campeonato guardado, pero falló la imagen.';
        this.loading = false;
        this.router.navigate(['/organizer/championships']);
      },
    });
  }

  isInvalid(field: string): boolean {
    const control = this.form.get(field);
    return !!(control && control.invalid && control.touched);
  }
}
