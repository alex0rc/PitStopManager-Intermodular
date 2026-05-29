import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { DatePipe } from '@angular/common';
import { ChampionshipService, StandingEntry } from '../../../core/services/championship.service';
import { RaceService } from '../../../core/services/race.service';
import { WeatherService } from '../../../core/services/weather.service';
import { Championship } from '../../../core/models/championship.model';
import { Race } from '../../../core/models/race.model';
import { RaceWithResults } from '../../../core/models/race-results.model';
import { WeatherData } from '../../../core/models/weather.model';
import { ChampionshipInscribeComponent } from '../../../shared/championship-inscribe/championship-inscribe.component';

@Component({
  selector: 'app-championship-detail',
  standalone: true,
  imports: [RouterLink, DatePipe, ChampionshipInscribeComponent],
  templateUrl: './championship-detail.component.html',
  styleUrl: './championship-detail.component.scss',
})
export class ChampionshipDetailComponent implements OnInit {
  championship: Championship | null = null;
  races: Race[] = [];
  standings: StandingEntry[] = [];
  raceResults: RaceWithResults[] = [];
  expandedRaceId: number | null = null;
  activeTab: 'races' | 'standings' = 'races';

  loading = true;
  racesLoading = true;
  standingsLoading = false;
  raceResultsLoading = false;
  error = '';

  weatherCache: Record<number, WeatherData> = {};
  weatherLoading: Record<number, boolean> = {};
  weatherError: Record<number, string> = {};

  constructor(
    private route: ActivatedRoute,
    private championshipService: ChampionshipService,
    private raceService: RaceService,
    private weatherService: WeatherService,
  ) {}

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    if (!id) {
      this.error = 'Campeonato no encontrado.';
      this.loading = false;
      return;
    }
    this.loadChampionship(id);
  }

  loadChampionship(id: number): void {
    this.championshipService.getById(id).subscribe({
      next: (champ) => {
        this.championship = champ;
        this.loading = false;
        this.loadRaces(id);
      },
      error: (err) => {
        const status = err?.status;
        if (status === 404) {
          this.error = 'Este campeonato no existe o ya no está disponible.';
        } else {
          this.error = err?.displayMessage || 'No se pudo cargar el campeonato. Inténtalo de nuevo.';
        }
        this.loading = false;
      },
    });
  }

  loadRaces(championshipId: number): void {
    this.racesLoading = true;
    this.raceService.getByChampionship(championshipId).subscribe({
      next: (races) => {
        this.races = races;
        this.racesLoading = false;
      },
      error: (err) => {
        console.error('Error loading races', err);
        this.racesLoading = false;
      },
    });
  }

  switchTab(tab: 'races' | 'standings'): void {
    this.activeTab = tab;
    if (tab === 'standings' && this.championship) {
      this.loadStandingsData(this.championship.id);
    }
  }

  loadStandingsData(championshipId: number): void {
    if (!this.standings.length) {
      this.standingsLoading = true;
      this.championshipService.getStandings(championshipId).subscribe({
        next: (data) => {
          this.standings = data;
          this.standingsLoading = false;
        },
        error: () => {
          this.standingsLoading = false;
        },
      });
    }

    if (!this.raceResults.length) {
      this.raceResultsLoading = true;
      this.championshipService.getRaceResults(championshipId).subscribe({
        next: (data) => {
          this.raceResults = data;
          if (data.length && this.expandedRaceId === null) {
            this.expandedRaceId = data[data.length - 1].race_id;
          }
          this.raceResultsLoading = false;
        },
        error: () => {
          this.raceResultsLoading = false;
        },
      });
    }
  }

  showRaceStandings(raceId: number): void {
    this.activeTab = 'standings';
    this.expandedRaceId = raceId;
    if (this.championship) {
      this.loadStandingsData(this.championship.id);
    }
  }

  toggleRaceExpand(raceId: number): void {
    this.expandedRaceId = this.expandedRaceId === raceId ? null : raceId;
  }

  pilotDisplayName(name: string | null): string {
    return name?.trim() || '—';
  }

  resultStatusLabel(dnf: boolean, dsq: boolean): string | null {
    if (dsq) return 'DSQ';
    if (dnf) return 'DNF';
    return null;
  }

  loadWeather(race: Race): void {
    const circuit = race.circuit;
    if (!circuit?.latitude || !circuit?.longitude) return;
    if (this.weatherCache[race.id] || this.weatherLoading[race.id]) return;

    this.weatherLoading[race.id] = true;
    this.weatherError[race.id] = '';

    this.weatherService.getWeather(circuit.latitude, circuit.longitude).subscribe({
      next: (data) => {
        if (data.error) {
          this.weatherError[race.id] = data.error;
        } else {
          this.weatherCache[race.id] = data;
        }
        this.weatherLoading[race.id] = false;
      },
      error: (err) => {
        this.weatherError[race.id] =
          err.error?.message || 'El tiempo no está disponible en este momento.';
        this.weatherLoading[race.id] = false;
      },
    });
  }

  statusLabel(status: string): string {
    const labels: Record<string, string> = {
      draft: 'Borrador',
      published: 'Publicado',
      in_progress: 'En curso',
      ongoing: 'En curso',
      finished: 'Finalizado',
      completed: 'Finalizado',
      cancelled: 'Cancelado',
    };
    return labels[status] ?? status;
  }

  raceStatusLabel(status: string): string {
    const labels: Record<string, string> = {
      scheduled: 'Programada',
      in_progress: 'En curso',
      completed: 'Completada',
      cancelled: 'Cancelada',
    };
    return labels[status] ?? status;
  }

  raceStatusClass(status: string): string {
    const classes: Record<string, string> = {
      scheduled: 'bg-info',
      in_progress: 'bg-warning text-dark',
      completed: 'bg-success',
      cancelled: 'bg-danger',
    };
    return classes[status] ?? 'bg-secondary';
  }
}
