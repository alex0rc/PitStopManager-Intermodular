import { Component, Input, OnChanges, OnDestroy, OnInit, SimpleChanges } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { Subject, takeUntil } from 'rxjs';
import { AuthService } from '../../core/services/auth.service';
import { InscriptionService } from '../../core/services/inscription.service';
import { RaceService } from '../../core/services/race.service';
import { Championship } from '../../core/models/championship.model';
import { Inscription } from '../../core/models/inscription.model';
import { Race } from '../../core/models/race.model';

@Component({
  selector: 'app-championship-inscribe',
  standalone: true,
  imports: [RouterLink, FormsModule],
  templateUrl: './championship-inscribe.component.html',
  styleUrl: './championship-inscribe.component.scss',
})
export class ChampionshipInscribeComponent implements OnInit, OnChanges, OnDestroy {
  @Input() layout: 'hero' | 'card' = 'hero';
  @Input({ required: true }) championship!: Championship;

  myInscription: Inscription | null = null;
  inscriptionLoading = false;
  inscribing = false;
  successMsg = '';
  errorMsg = '';
  showModal = false;
  modalRaces: Race[] = [];
  modalRacesLoading = false;
  selectedRaceIds = new Set<number>();
  modalCarNumber: number | null = null;
  modalKartInfo = '';

  private destroy$ = new Subject<void>();
  returnUrl = '';

  constructor(
    private router: Router,
    public auth: AuthService,
    private inscriptionService: InscriptionService,
    private raceService: RaceService,
  ) {}

  ngOnInit(): void {
    this.returnUrl = this.router.url;
    this.auth.currentUser$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => this.refreshInscription());

    this.refreshInscription();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['championship'] && !changes['championship'].firstChange) {
      this.refreshInscription();
    }
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  get isPilot(): boolean {
    return this.auth.isLoggedIn && this.auth.hasRole('pilot');
  }

  get isOrganizerNotOwner(): boolean {
    if (!this.auth.isLoggedIn || !this.auth.hasRole('organizer') || !this.championship) {
      return false;
    }
    const uid = this.auth.currentUser?.id;
    return uid != null && this.championship.user_id !== uid;
  }

  get isOpenForInscription(): boolean {
    return this.championship?.status === 'published';
  }

  get canInscribe(): boolean {
    if (!this.isOpenForInscription) return false;
    return this.isPilot || this.isOrganizerNotOwner;
  }

  get isChampionshipOwner(): boolean {
    if (!this.auth.isLoggedIn || !this.championship) return false;
    const uid = this.auth.currentUser?.id;
    return uid != null && this.championship.user_id === uid;
  }

  get inscribeBlockedHint(): string | null {
    if (!this.auth.isLoggedIn || !this.isOpenForInscription || this.canInscribe) return null;
    if (this.isChampionshipOwner) {
      return 'No puedes inscribirte en un campeonato que organizas.';
    }
    if (this.auth.hasRole('admin')) {
      return 'Los administradores gestionan el sistema desde el panel admin.';
    }
    if (this.auth.hasRole('organizer')) {
      return 'Completa tu perfil de piloto en Mi Perfil para inscribirte aquí.';
    }
    return 'Inicia sesión como piloto u organizador para inscribirte.';
  }

  get requiresOwnKart(): boolean {
    return this.championship?.kart_modality === 'own';
  }

  get kartModalityLabel(): string {
    return this.requiresOwnKart ? 'Kart propio' : 'Karts de alquiler';
  }

  refreshInscription(): void {
    if (!this.canInscribe || !this.championship?.id) {
      this.myInscription = null;
      this.inscriptionLoading = false;
      return;
    }

    this.inscriptionLoading = true;
    this.inscriptionService.getMyInscriptions().subscribe({
      next: (list) => {
        const found = list.find(
          (i) =>
            i.championship_id === this.championship.id &&
            i.status !== 'withdrawn' &&
            i.status !== 'rejected',
        );
        this.myInscription = found ?? null;
        this.inscriptionLoading = false;
      },
      error: () => {
        this.inscriptionLoading = false;
      },
    });
  }

  canEditRaces(insc: Inscription): boolean {
    return insc.status === 'pending' || insc.status === 'confirmed';
  }

  openModal(event?: Event): void {
    event?.preventDefault();
    event?.stopPropagation();

    this.showModal = true;
    this.modalCarNumber = this.myInscription?.car_number ?? null;
    this.modalKartInfo = this.myInscription?.kart_info ?? '';
    this.selectedRaceIds = new Set(this.myInscription?.races?.map((r) => r.id) ?? []);
    this.errorMsg = '';
    this.modalRaces = [];
    this.modalRacesLoading = true;

    this.raceService.getByChampionship(this.championship.id).subscribe({
      next: (races) => {
        this.modalRaces = races.filter(
          (r) => r.status === 'scheduled' || r.status === 'in_progress',
        );
        if (!this.myInscription && this.selectedRaceIds.size === 0) {
          for (const r of this.modalRaces) {
            this.selectedRaceIds.add(r.id);
          }
        }
        this.modalRacesLoading = false;
      },
      error: () => {
        this.modalRacesLoading = false;
        this.errorMsg = 'No se pudieron cargar las carreras.';
      },
    });
  }

  closeModal(): void {
    this.showModal = false;
    this.modalRaces = [];
    this.selectedRaceIds = new Set();
    this.modalCarNumber = null;
    this.modalKartInfo = '';
    this.errorMsg = '';
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

  submit(): void {
    const raceIds = [...this.selectedRaceIds];
    if (this.modalRaces.length > 0 && raceIds.length === 0) {
      this.errorMsg = 'Selecciona al menos una carrera.';
      return;
    }

    this.inscribing = true;
    this.successMsg = '';
    this.errorMsg = '';

    if (this.requiresOwnKart && !this.modalKartInfo.trim()) {
      this.errorMsg = 'Indica el modelo de tu kart (chasis y motor).';
      return;
    }

    const isEdit = !!this.myInscription;
    const payload = {
      race_ids: raceIds,
      car_number: this.modalCarNumber ?? undefined,
      kart_info: this.requiresOwnKart ? this.modalKartInfo.trim() : undefined,
    };

    const request = isEdit
      ? this.inscriptionService.updateRaces(this.myInscription!.id, payload)
      : this.inscriptionService.create(this.championship.id, payload);

    request.subscribe({
      next: (inscription) => {
        this.myInscription = inscription;
        this.inscribing = false;
        this.successMsg = isEdit
          ? 'Carreras actualizadas correctamente.'
          : 'Inscripción enviada correctamente.';
        this.closeModal();
      },
      error: (err) => {
        this.inscribing = false;
        this.errorMsg =
          err?.error?.errors?.race_ids?.[0] ??
          err?.error?.message ??
          err?.displayMessage ??
          'Error al guardar la inscripción.';
      },
    });
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

  raceNames(insc: Inscription): string {
    if (!insc.races?.length) return '';
    return insc.races.map((r) => r.name).join(', ');
  }

  formatRaceDate(scheduledAt: string): string {
    return new Date(scheduledAt).toLocaleDateString('es-ES', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    });
  }
}
