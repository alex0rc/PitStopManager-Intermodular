import { Component, OnInit, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { DatePipe } from '@angular/common';
import { forkJoin } from 'rxjs';
import { AuthService } from '../../../core/services/auth.service';
import { InscriptionService } from '../../../core/services/inscription.service';
import { ChampionshipService } from '../../../core/services/championship.service';
import { Inscription } from '../../../core/models/inscription.model';
import { Championship } from '../../../core/models/championship.model';

@Component({
  selector: 'app-pilot-dashboard',
  standalone: true,
  imports: [RouterLink, DatePipe],
  templateUrl: './pilot-dashboard.component.html',
  styleUrl: './pilot-dashboard.component.scss',
})
export class PilotDashboardComponent implements OnInit {
  private auth = inject(AuthService);
  private inscriptionService = inject(InscriptionService);
  private championshipService = inject(ChampionshipService);

  user = this.auth.currentUser;
  loading = true;

  inscriptions: Inscription[] = [];
  openChampionships: Championship[] = [];
  loadError = '';

  ngOnInit(): void {
    this.loading = true;
    this.loadError = '';

    forkJoin({
      inscriptions: this.inscriptionService.getMyInscriptions(),
      championships: this.championshipService.getAll({ status: 'published', per_page: 4 }),
    }).subscribe({
      next: ({ inscriptions, championships }) => {
        this.inscriptions = inscriptions;
        this.openChampionships = championships.data ?? [];
        this.loading = false;
      },
      error: () => {
        this.loadError = 'No se pudieron cargar tus datos. Comprueba la conexión con el servidor.';
        this.loading = false;
      },
    });
  }

  get firstName(): string {
    return (this.user?.name ?? '').split(/\s+/)[0] || 'Piloto';
  }

  get totalInscriptions(): number {
    return this.inscriptions.length;
  }

  get confirmedCount(): number {
    return this.inscriptions.filter((i) => i.status === 'confirmed').length;
  }

  get pendingCount(): number {
    return this.inscriptions.filter((i) => i.status === 'pending').length;
  }

  recentInscriptions(): Inscription[] {
    return this.inscriptions.slice(0, 5);
  }

  inscriptionStatusClass(status: string): string {
    return `status-pill status-${status}`;
  }

  inscriptionStatusLabel(status: string): string {
    const labels: Record<string, string> = {
      pending: 'Pendiente',
      confirmed: 'Confirmada',
      rejected: 'Rechazada',
      withdrawn: 'Retirada',
    };
    return labels[status] ?? status;
  }
}
