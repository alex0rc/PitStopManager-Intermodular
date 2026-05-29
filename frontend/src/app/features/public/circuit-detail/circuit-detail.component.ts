import { Component, OnInit, inject } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { CircuitService } from '../../../core/services/circuit.service';
import { WeatherService } from '../../../core/services/weather.service';
import { AuthService } from '../../../core/services/auth.service';
import { Circuit } from '../../../core/models/circuit.model';
import { WeatherData } from '../../../core/models/weather.model';

@Component({
  selector: 'app-circuit-detail',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './circuit-detail.component.html',
  styleUrl: './circuit-detail.component.scss',
})
export class CircuitDetailComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private circuitService = inject(CircuitService);
  private weatherService = inject(WeatherService);
  auth = inject(AuthService);

  circuit: Circuit | null = null;
  loading = true;
  error = '';

  weather: WeatherData | null = null;
  weatherLoading = false;
  weatherError = '';

  backLink = '/circuits';

  ngOnInit(): void {
    this.backLink = this.resolveBackLink();
    const id = Number(this.route.snapshot.paramMap.get('id'));
    if (!id) {
      this.error = 'Circuito no encontrado.';
      this.loading = false;
      return;
    }
    this.loadCircuit(id);
  }

  private resolveBackLink(): string {
    if (!this.auth.isLoggedIn) return '/circuits';
    const role = this.auth.currentUser?.role;
    if (role === 'organizer') return '/organizer/circuits';
    return '/circuits';
  }

  loadCircuit(id: number): void {
    this.circuitService.getById(id).subscribe({
      next: (circuit) => {
        this.circuit = circuit;
        this.loading = false;
        if (circuit.latitude != null && circuit.longitude != null) {
          this.loadWeather(circuit.latitude, circuit.longitude);
        }
      },
      error: (err) => {
        this.error = err?.error?.message || err.displayMessage || 'No se pudo cargar el circuito.';
        this.loading = false;
      },
    });
  }

  private loadWeather(lat: number, lng: number): void {
    this.weatherLoading = true;
    this.weatherError = '';
    this.weatherService.getWeather(lat, lng).subscribe({
      next: (data) => {
        if (data.error) {
          this.weatherError = data.error;
        } else {
          this.weather = data;
        }
        this.weatherLoading = false;
      },
      error: () => {
        this.weatherError = 'No se pudo cargar el tiempo.';
        this.weatherLoading = false;
      },
    });
  }

  get canEdit(): boolean {
    if (!this.circuit || !this.auth.isLoggedIn) return false;
    const user = this.auth.currentUser;
    if (!user) return false;
    return (
      (user.role === 'organizer' && user.id === this.circuit.user_id && this.circuit.status !== 'approved') ||
      user.role === 'admin'
    );
  }

  statusLabel(status?: string): string {
    const labels: Record<string, string> = {
      pending: 'Pendiente de aprobación',
      approved: 'Aprobado',
      rejected: 'Rechazado',
    };
    return labels[status ?? ''] ?? status ?? '';
  }

  statusClass(status?: string): string {
    const map: Record<string, string> = {
      pending: 'status-pending',
      approved: 'status-confirmed',
      rejected: 'status-rejected',
    };
    return map[status ?? ''] ?? '';
  }

  locationLine(): string {
    if (!this.circuit) return '';
    const parts = [this.circuit.city, this.circuit.province, this.circuit.country].filter(Boolean);
    return parts.join(', ') || this.circuit.location;
  }

  googleMapsUrl(): string | null {
    if (!this.circuit) return null;

    const lat = this.circuit.latitude;
    const lng = this.circuit.longitude;
    if (lat != null && lng != null) {
      return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
    }

    const query = [this.circuit.location, this.circuit.city, this.circuit.province, this.circuit.country]
      .filter(Boolean)
      .join(', ')
      .trim();

    if (!query) return null;

    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
  }
}
