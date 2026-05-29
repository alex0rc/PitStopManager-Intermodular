import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { InscriptionService } from '../../../../core/services/inscription.service';
import { Inscription } from '../../../../core/models/inscription.model';

@Component({
  selector: 'app-inscription-list',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './inscription-list.component.html',
  styleUrl: './inscription-list.component.scss',
})
export class InscriptionListComponent implements OnInit {
  inscriptions: Inscription[] = [];
  championshipId!: number;
  loading = true;
  error = '';
  successMsg = '';

  constructor(
    private route: ActivatedRoute,
    private inscriptionService: InscriptionService,
  ) {}

  ngOnInit(): void {
    this.championshipId = +this.route.snapshot.paramMap.get('championshipId')!;
    this.loadInscriptions();
  }

  loadInscriptions(): void {
    this.loading = true;
    this.error = '';
    this.inscriptionService.getByChampionship(this.championshipId).subscribe({
      next: (inscriptions) => {
        this.inscriptions = inscriptions;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al cargar las inscripciones.';
        this.loading = false;
      },
    });
  }

  getStatusBadgeClass(status: string): string {
    const map: Record<string, string> = {
      pending: 'bg-warning text-dark',
      confirmed: 'bg-success',
      rejected: 'bg-danger',
      withdrawn: 'bg-secondary',
    };
    return map[status] || 'bg-secondary';
  }

  getStatusLabel(status: string): string {
    const labels: Record<string, string> = {
      pending: 'Pendiente',
      confirmed: 'Confirmada',
      rejected: 'Rechazada',
      withdrawn: 'Retirada',
    };
    return labels[status] || status;
  }

  approve(inscription: Inscription): void {
    this.inscriptionService.updateStatus(inscription.id, 'confirmed').subscribe({
      next: (updated) => this.replaceInscription(updated),
      error: (err) => {
        this.error = err.displayMessage || 'Error al aprobar la inscripción.';
      },
    });
  }

  reject(inscription: Inscription): void {
    if (!confirm(`¿Rechazar la inscripción de ${inscription.user?.name ?? 'este piloto'}?`)) {
      return;
    }
    this.inscriptionService.updateStatus(inscription.id, 'rejected').subscribe({
      next: (updated) => this.replaceInscription(updated),
      error: (err) => {
        this.error = err.displayMessage || 'Error al rechazar la inscripción.';
      },
    });
  }

  removeFromChampionship(inscription: Inscription): void {
    if (!confirm(
      `¿Quitar a ${inscription.user?.name ?? 'este piloto'} del campeonato? Se eliminará su inscripción por completo.`,
    )) {
      return;
    }
    this.inscriptionService.remove(inscription.id).subscribe({
      next: () => {
        this.inscriptions = this.inscriptions.filter((i) => i.id !== inscription.id);
        this.successMsg = 'Inscripción eliminada del campeonato.';
      },
      error: (err) => {
        this.error = err.displayMessage || 'Error al eliminar la inscripción.';
      },
    });
  }

  removeFromRace(inscription: Inscription, raceId: number, raceName: string): void {
    if (!confirm(`¿Quitar a ${inscription.user?.name ?? 'el piloto'} de la carrera «${raceName}»?`)) {
      return;
    }
    this.inscriptionService.detachRace(inscription.id, raceId).subscribe({
      next: (updated) => {
        this.replaceInscription(updated);
        this.successMsg = `Piloto quitado de «${raceName}».`;
      },
      error: (err) => {
        this.error = err.displayMessage || 'Error al quitar de la carrera.';
      },
    });
  }

  raceNames(insc: Inscription): string {
    if (!insc.races?.length) return '—';
    return insc.races.map((r) => r.name).join(', ');
  }

  canManage(insc: Inscription): boolean {
    return !['rejected', 'withdrawn'].includes(insc.status);
  }

  private replaceInscription(updated: Inscription): void {
    const idx = this.inscriptions.findIndex((i) => i.id === updated.id);
    if (idx !== -1) {
      this.inscriptions[idx] = updated;
    }
  }
}
