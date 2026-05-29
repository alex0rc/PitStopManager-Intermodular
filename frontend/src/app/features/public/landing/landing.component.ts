import { Component, OnInit, inject } from '@angular/core';
import { NgClass } from '@angular/common';
import { RouterLink } from '@angular/router';
import { BrandLogoComponent } from '../../../shared/brand-logo/brand-logo.component';
import { ChampionshipService } from '../../../core/services/championship.service';
import { FeaturedChampionshipPayload, FeaturedStanding } from '../../../core/models/featured-championship.model';
import { WeatherData } from '../../../core/models/weather.model';

@Component({
  selector: 'app-landing',
  standalone: true,
  imports: [RouterLink, NgClass, BrandLogoComponent],
  templateUrl: './landing.component.html',
  styleUrl: './landing.component.scss',
})
export class LandingComponent implements OnInit {
  private championshipService = inject(ChampionshipService);

  featured: FeaturedChampionshipPayload | null = null;
  loadingFeatured = true;
  featuredError = '';

  ngOnInit(): void {
    this.championshipService.getFeatured().subscribe({
      next: (data) => {
        this.featured = data;
        this.loadingFeatured = false;
      },
      error: () => {
        this.featuredError = 'No se pudo cargar el campeonato destacado.';
        this.loadingFeatured = false;
      },
    });
  }

  statusLabel(status: string): string {
    const labels: Record<string, string> = {
      draft: 'Borrador',
      published: 'Publicado',
      in_progress: 'En curso',
      finished: 'Finalizado',
      cancelled: 'Cancelado',
      scheduled: 'Programada',
      completed: 'Completada',
    };
    return labels[status] ?? status;
  }

  pilotShortName(name: string): string {
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0];
    return `${parts[0]} ${parts[parts.length - 1].charAt(0)}.`;
  }

  rankClass(index: number): string {
    if (index === 0) return 'gold';
    if (index === 1) return 'silver';
    if (index === 2) return 'bronze';
    return 'muted';
  }

  pointsLabel(entry: FeaturedStanding, index: number, standings: FeaturedStanding[]): string {
    if (index === 0 || !standings.length) {
      return `${entry.total_points} pts`;
    }
    const leader = standings[0].total_points;
    const diff = leader - entry.total_points;
    return diff > 0 ? `-${diff} pts` : `${entry.total_points} pts`;
  }

  circuitLocation(): string {
    const circuit = this.featured?.next_race?.circuit;
    if (!circuit) return '';
    const bits = [circuit.name, circuit.city, circuit.province].filter(Boolean);
    return bits.join(' · ');
  }

  weatherLine(weather: WeatherData | null | undefined): string {
    if (!weather) return 'Meteorología no disponible';
    if (weather.error) return 'Clima: configuración pendiente';
    const parts: string[] = [];
    if (weather.temperature !== null) parts.push(`${Math.round(weather.temperature)}°C`);
    if (weather.wind_speed !== null) parts.push(`Viento ${weather.wind_speed} km/h`);
    if (weather.description) parts.push(weather.description);
    return parts.length ? parts.join(' · ') : 'Meteorología no disponible';
  }
}
