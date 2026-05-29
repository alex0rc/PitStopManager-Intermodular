import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { DatePipe } from '@angular/common';
import { ChampionshipService } from '../../../../core/services/championship.service';
import { RaceService } from '../../../../core/services/race.service';
import { Championship } from '../../../../core/models/championship.model';
import { Race } from '../../../../core/models/race.model';

@Component({
  selector: 'app-championship-races',
  standalone: true,
  imports: [RouterLink, DatePipe],
  templateUrl: './championship-races.component.html',
  styleUrls: ['./championship-races.component.scss']
})
export class ChampionshipRacesComponent implements OnInit {
  championship?: Championship;
  races: Race[] = [];
  loading = true;
  error = '';
  championshipId!: number;

  constructor(
    private route: ActivatedRoute,
    private championshipService: ChampionshipService,
    private raceService: RaceService
  ) {}

  ngOnInit(): void {
    this.championshipId = +this.route.snapshot.paramMap.get('championshipId')!;
    this.loadData();
  }

  loadData(): void {
    this.loading = true;
    this.error = '';

    this.championshipService.getById(this.championshipId).subscribe({
      next: (champ) => {
        this.championship = champ;
        this.loadRaces();
      },
      error: () => {
        this.error = 'Error al cargar el campeonato.';
        this.loading = false;
      }
    });
  }

  private loadRaces(): void {
    this.raceService.getByChampionship(this.championshipId).subscribe({
      next: (races) => {
        this.races = races;
        this.loading = false;
      },
      error: () => {
        this.error = 'Error al cargar las carreras.';
        this.loading = false;
      }
    });
  }

  getStatusBadgeClass(status: string): string {
    const map: Record<string, string> = {
      scheduled: 'bg-info',
      in_progress: 'bg-primary',
      completed: 'bg-success',
      cancelled: 'bg-danger'
    };
    return map[status] || 'bg-secondary';
  }

  deleteRace(id: number): void {
    if (!confirm('¿Estás seguro de que deseas eliminar esta carrera?')) return;
    this.raceService.delete(id).subscribe({
      next: () => {
        this.races = this.races.filter(r => r.id !== id);
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al eliminar la carrera.';
      }
    });
  }
}
