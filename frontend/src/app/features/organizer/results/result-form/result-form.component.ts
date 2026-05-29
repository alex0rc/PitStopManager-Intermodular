import { Component, OnInit } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { ResultService } from '../../../../core/services/result.service';
import { RaceService } from '../../../../core/services/race.service';
import { InscriptionService } from '../../../../core/services/inscription.service';
import { Inscription } from '../../../../core/models/inscription.model';

@Component({
  selector: 'app-result-form',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './result-form.component.html',
  styleUrls: ['./result-form.component.scss']
})
export class ResultFormComponent implements OnInit {
  form!: FormGroup;
  pilots: Inscription[] = [];
  isEdit = false;
  resultId?: number;
  raceId!: number;
  loading = false;
  loadingData = true;
  error = '';

  constructor(
    private fb: FormBuilder,
    private resultService: ResultService,
    private raceService: RaceService,
    private inscriptionService: InscriptionService,
    private route: ActivatedRoute,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.raceId = +this.route.snapshot.paramMap.get('raceId')!;

    this.form = this.fb.group({
      user_id: [null, Validators.required],
      position: [null, [Validators.min(1)]],
      best_lap_time: [''],
      total_time: [''],
      points: [0, [Validators.required, Validators.min(0)]],
      dnf: [false],
      dsq: [false],
      notes: ['']
    });

    this.raceService.getById(this.raceId).subscribe({
      next: (race) => {
        this.inscriptionService.getByChampionship(race.championship_id).subscribe({
          next: (inscriptions) => {
            this.pilots = inscriptions.filter(
              (i) =>
                i.status === 'confirmed' &&
                (i.races?.some((r) => r.id === this.raceId) ?? false),
            );
            this.checkEditMode();
          },
          error: () => {
            this.error = 'Error al cargar los pilotos inscritos.';
            this.loadingData = false;
          }
        });
      },
      error: () => {
        this.error = 'Error al cargar la carrera.';
        this.loadingData = false;
      }
    });
  }

  private checkEditMode(): void {
    const resultId = this.route.snapshot.paramMap.get('resultId');
    if (resultId) {
      this.isEdit = true;
      this.resultId = +resultId;
      const existing = this.resultService;
      existing.getByRace(this.raceId).subscribe({
        next: (results) => {
          const result = results.find(r => r.id === this.resultId);
          if (result) {
            this.form.patchValue({
              user_id: result.user_id,
              position: result.position,
              best_lap_time: result.best_lap_time || '',
              total_time: result.total_time || '',
              points: result.points,
              dnf: result.dnf,
              dsq: result.dsq,
              notes: result.notes || ''
            });
          }
          this.loadingData = false;
        },
        error: () => {
          this.error = 'Error al cargar el resultado.';
          this.loadingData = false;
        }
      });
    } else {
      this.loadingData = false;
    }
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.loading = true;
    this.error = '';
    const data = this.form.value;

    const request$ = this.isEdit
      ? this.resultService.update(this.resultId!, data)
      : this.resultService.create(this.raceId, data);

    request$.subscribe({
      next: () => {
        this.router.navigate(['/organizer/races', this.raceId, 'results']);
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al guardar el resultado.';
        this.loading = false;
      }
    });
  }

  isInvalid(field: string): boolean {
    const control = this.form.get(field);
    return !!(control && control.invalid && control.touched);
  }
}
