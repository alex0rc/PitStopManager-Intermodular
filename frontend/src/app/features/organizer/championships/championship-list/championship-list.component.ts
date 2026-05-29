import { Component, OnInit, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { ChampionshipService } from '../../../../core/services/championship.service';
import { SubscriptionService } from '../../../../core/services/subscription.service';
import { AuthService } from '../../../../core/services/auth.service';
import { SubscriptionQuota } from '../../../../core/models/subscription.model';
import { Championship } from '../../../../core/models/championship.model';

@Component({
  selector: 'app-championship-list',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './championship-list.component.html',
  styleUrls: ['./championship-list.component.scss']
})
export class ChampionshipListComponent implements OnInit {
  championships: Championship[] = [];
  loading = true;
  error = '';
  currentPage = 1;
  lastPage = 1;
  quota?: SubscriptionQuota;

  private auth = inject(AuthService);

  constructor(
    private championshipService: ChampionshipService,
    private subscriptionService: SubscriptionService,
  ) {}

  isOwner(champ: Championship): boolean {
    return champ.user_id === this.auth.currentUser?.id;
  }

  ngOnInit(): void {
    this.subscriptionService.getMySubscriptionWithQuota().subscribe({
      next: ({ quota }) => (this.quota = quota),
    });
    this.loadChampionships();
  }

  get canCreateChampionship(): boolean {
    return this.quota?.can_create_championship ?? true;
  }

  loadChampionships(): void {
    this.loading = true;
    this.error = '';
    this.championshipService.getMine({ page: this.currentPage }).subscribe({
      next: (res) => {
        this.championships = res.data;
        this.currentPage = res.meta.current_page;
        this.lastPage = res.meta.last_page;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al cargar los campeonatos.';
        this.loading = false;
      }
    });
  }

  goToPage(page: number): void {
    if (page >= 1 && page <= this.lastPage) {
      this.currentPage = page;
      this.loadChampionships();
    }
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

  getStatusBadgeClass(status: string): string {
    const map: Record<string, string> = {
      draft: 'bg-secondary',
      published: 'bg-info',
      in_progress: 'bg-primary',
      finished: 'bg-success',
      cancelled: 'bg-danger'
    };
    return map[status] || 'bg-secondary';
  }

  changeStatus(championship: Championship, newStatus: string): void {
    this.championshipService.updateStatus(championship.id, newStatus).subscribe({
      next: (updated) => {
        const idx = this.championships.findIndex(c => c.id === updated.id);
        if (idx !== -1) this.championships[idx] = updated;
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al cambiar el estado.';
      }
    });
  }

  deleteChampionship(id: number): void {
    if (!confirm('¿Estás seguro de que deseas eliminar este campeonato?')) return;
    this.championshipService.delete(id).subscribe({
      next: () => {
        this.championships = this.championships.filter(c => c.id !== id);
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al eliminar el campeonato.';
      }
    });
  }

  get pages(): number[] {
    return Array.from({ length: this.lastPage }, (_, i) => i + 1);
  }
}
