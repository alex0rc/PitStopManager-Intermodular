import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { InscriptionService } from '../../../../core/services/inscription.service';
import { RaceService } from '../../../../core/services/race.service';
import { Inscription } from '../../../../core/models/inscription.model';
import { Race } from '../../../../core/models/race.model';

@Component({
  selector: 'app-pilot-inscription-list',
  standalone: true,
  imports: [RouterLink, FormsModule],
  templateUrl: './pilot-inscription-list.component.html',
  styleUrl: './pilot-inscription-list.component.scss',
})
export class PilotInscriptionListComponent implements OnInit {
  inscriptions: Inscription[] = [];
  loading = true;
  error = '';

  withdrawing: Record<number, boolean> = {};
  successMsg = '';
  errorMsg = '';

  editModalInscription: Inscription | null = null;
  editModalRaces: Race[] = [];
  editModalRacesLoading = false;
  selectedRaceIds = new Set<number>();
  editModalKartInfo = '';
  savingRaces = false;

  constructor(
    private inscriptionService: InscriptionService,
    private raceService: RaceService,
  ) {}

  ngOnInit(): void {
    this.loadInscriptions();
  }

  loadInscriptions(): void {
    this.loading = true;
    this.error = '';
    this.inscriptionService.getMyInscriptions().subscribe({
      next: (data) => {
        this.inscriptions = data;
        this.loading = false;
      },
      error: () => {
        this.error = 'No se pudieron cargar las inscripciones.';
        this.loading = false;
      },
    });
  }

  canWithdraw(inscription: Inscription): boolean {
    return inscription.status === 'pending' || inscription.status === 'confirmed';
  }

  canEditRaces(inscription: Inscription): boolean {
    return inscription.status === 'pending' || inscription.status === 'confirmed';
  }

  raceNames(insc: Inscription): string {
    if (!insc.races?.length) return '—';
    return insc.races.map((r) => r.name).join(', ');
  }

  usesOwnKart(insc: Inscription): boolean {
    return insc.championship?.kart_modality === 'own';
  }

  kartDisplay(insc: Inscription): string {
    if (this.usesOwnKart(insc)) {
      return insc.kart_info?.trim() || '— (sin datos)';
    }
    return 'Alquiler en circuito';
  }

  openEditRaces(insc: Inscription): void {
    this.editModalInscription = insc;
    this.editModalKartInfo = insc.kart_info ?? '';
    this.selectedRaceIds = new Set(insc.races?.map((r) => r.id) ?? []);
    this.editModalRaces = [];
    this.editModalRacesLoading = true;
    this.errorMsg = '';

    this.raceService.getByChampionship(insc.championship_id).subscribe({
      next: (races) => {
        this.editModalRaces = races.filter(
          (r) => r.status === 'scheduled' || r.status === 'in_progress',
        );
        this.editModalRacesLoading = false;
      },
      error: () => {
        this.editModalRacesLoading = false;
        this.errorMsg = 'No se pudieron cargar las carreras.';
      },
    });
  }

  closeEditRaces(): void {
    this.editModalInscription = null;
    this.editModalRaces = [];
    this.selectedRaceIds = new Set();
    this.editModalKartInfo = '';
  }

  toggleRace(raceId: number, checked: boolean): void {
    if (checked) {
      this.selectedRaceIds.add(raceId);
    } else {
      this.selectedRaceIds.delete(raceId);
    }
  }

  isRaceSelected(raceId: number): boolean {
    return this.selectedRaceIds.has(raceId);
  }

  saveRaces(): void {
    if (!this.editModalInscription) return;
    const raceIds = [...this.selectedRaceIds];
    if (this.editModalRaces.length > 0 && raceIds.length === 0) {
      this.errorMsg = 'Selecciona al menos una carrera.';
      return;
    }

    if (this.usesOwnKart(this.editModalInscription) && !this.editModalKartInfo.trim()) {
      this.errorMsg = 'Indica el modelo de tu kart (chasis y motor).';
      return;
    }

    this.savingRaces = true;
    const payload: { race_ids: number[]; kart_info?: string } = { race_ids: raceIds };
    if (this.usesOwnKart(this.editModalInscription)) {
      payload.kart_info = this.editModalKartInfo.trim();
    }

    this.inscriptionService.updateRaces(this.editModalInscription.id, payload).subscribe({
      next: (updated) => {
        const idx = this.inscriptions.findIndex((i) => i.id === updated.id);
        if (idx !== -1) {
          this.inscriptions[idx] = updated;
        }
        this.savingRaces = false;
        this.successMsg = 'Carreras actualizadas correctamente.';
        this.closeEditRaces();
      },
      error: (err) => {
        this.savingRaces = false;
        this.errorMsg =
          err?.error?.errors?.race_ids?.[0] || 'Error al actualizar las carreras.';
      },
    });
  }

  withdraw(inscription: Inscription): void {
    if (!confirm('¿Estás seguro de que quieres retirar tu inscripción?')) return;

    this.withdrawing[inscription.id] = true;
    this.successMsg = '';
    this.errorMsg = '';

    this.inscriptionService.withdraw(inscription.id).subscribe({
      next: (updated) => {
        const idx = this.inscriptions.findIndex((i) => i.id === inscription.id);
        if (idx !== -1) {
          this.inscriptions[idx] = { ...this.inscriptions[idx], ...updated, status: 'withdrawn' };
        }
        this.withdrawing[inscription.id] = false;
        this.successMsg = 'Inscripción retirada correctamente.';
      },
      error: (err) => {
        this.withdrawing[inscription.id] = false;
        this.errorMsg = err?.displayMessage || 'Error al retirar la inscripción.';
      },
    });
  }

  statusLabel(status: string): string {
    const labels: Record<string, string> = {
      pending: 'Pendiente',
      confirmed: 'Confirmada',
      rejected: 'Rechazada',
      withdrawn: 'Retirada',
    };
    return labels[status] ?? status;
  }

  statusClass(status: string): string {
    const classes: Record<string, string> = {
      pending: 'bg-warning text-dark',
      confirmed: 'bg-success',
      rejected: 'bg-danger',
      withdrawn: 'bg-secondary',
    };
    return classes[status] ?? 'bg-secondary';
  }

  formatRaceDate(scheduledAt: string): string {
    return new Date(scheduledAt).toLocaleDateString('es-ES', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    });
  }
}
