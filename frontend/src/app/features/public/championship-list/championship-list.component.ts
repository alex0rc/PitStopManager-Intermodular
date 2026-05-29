import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { ChampionshipService } from '../../../core/services/championship.service';
import { Championship } from '../../../core/models/championship.model';
import { ChampionshipInscribeComponent } from '../../../shared/championship-inscribe/championship-inscribe.component';

@Component({
  selector: 'app-championship-list',
  standalone: true,
  imports: [RouterLink, FormsModule, ChampionshipInscribeComponent],
  templateUrl: './championship-list.component.html',
  styleUrl: './championship-list.component.scss',
})
export class ChampionshipListComponent implements OnInit {
  championships: Championship[] = [];
  filtered: Championship[] = [];
  searchTerm = '';
  loading = true;
  error = '';

  constructor(private championshipService: ChampionshipService) {}

  ngOnInit(): void {
    this.loadChampionships();
  }

  loadChampionships(): void {
    this.loading = true;
    this.error = '';
    this.championshipService.getAll({ status: 'published' }).subscribe({
      next: (res) => {
        this.championships = res.data;
        this.filtered = res.data;
        this.loading = false;
      },
      error: () => {
        this.error = 'No se pudieron cargar los campeonatos. Inténtalo de nuevo.';
        this.loading = false;
      },
    });
  }

  onSearch(): void {
    const term = this.searchTerm.toLowerCase().trim();
    if (!term) {
      this.filtered = this.championships;
      return;
    }
    this.filtered = this.championships.filter(
      (c) =>
        c.name.toLowerCase().includes(term) ||
        c.category?.name?.toLowerCase().includes(term) ||
        c.season_year.toString().includes(term),
    );
  }

  statusLabel(status: string): string {
    const labels: Record<string, string> = {
      draft: 'Borrador',
      published: 'Publicado',
      in_progress: 'En curso',
      finished: 'Finalizado',
      cancelled: 'Cancelado',
    };
    return labels[status] ?? status;
  }
}
