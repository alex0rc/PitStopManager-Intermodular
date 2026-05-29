import { Component, OnInit } from '@angular/core';
import { forkJoin } from 'rxjs';
import { FormsModule } from '@angular/forms';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { RaceService } from '../../../../core/services/race.service';
import { CircuitService } from '../../../../core/services/circuit.service';
import { Circuit } from '../../../../core/models/circuit.model';

@Component({
  selector: 'app-race-form',
  standalone: true,
  imports: [ReactiveFormsModule, FormsModule, RouterLink],
  templateUrl: './race-form.component.html',
  styleUrls: ['./race-form.component.scss']
})
export class RaceFormComponent implements OnInit {
  form!: FormGroup;
  circuits: Circuit[] = [];
  provinces: string[] = [];
  filterProvince = '';
  isEdit = false;
  raceId?: number;
  championshipId!: number;
  loading = false;
  loadingData = true;
  error = '';

  constructor(
    private fb: FormBuilder,
    private raceService: RaceService,
    private circuitService: CircuitService,
    private route: ActivatedRoute,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.championshipId = +this.route.snapshot.paramMap.get('championshipId')!;

    this.form = this.fb.group({
      name: ['', [Validators.required, Validators.maxLength(255)]],
      circuit_id: [null, Validators.required],
      scheduled_at: ['', Validators.required],
      total_laps: [null, [Validators.min(1)]],
      notes: ['']
    });

    const raceIdParam = this.route.snapshot.paramMap.get('id');
    if (raceIdParam) {
      this.isEdit = true;
      this.raceId = +raceIdParam;
    }

    this.circuitService.getProvinces().subscribe({
      next: (list) => (this.provinces = list),
      error: () => {},
    });
    this.loadCircuits();
  }

  loadCircuits(): void {
    const params: Record<string, string | number> = { per_page: 100 };
    if (this.filterProvince) params['province'] = this.filterProvince;

    forkJoin({
      catalog: this.circuitService.getAll(params),
      mine: this.circuitService.getMine({ per_page: 100 }),
    }).subscribe({
      next: ({ catalog, mine }) => {
        const byId = new Map<number, Circuit>();
        for (const c of catalog.data) byId.set(c.id, c);
        for (const c of mine.data) {
          if (!byId.has(c.id)) byId.set(c.id, c);
        }
        this.circuits = [...byId.values()].sort((a, b) =>
          (a.province ?? '').localeCompare(b.province ?? '') || a.name.localeCompare(b.name),
        );
        if (this.isEdit && this.raceId) {
          this.loadRace();
        } else {
          this.loadingData = false;
        }
      },
      error: () => {
        this.error = 'Error al cargar los circuitos.';
        this.loadingData = false;
      },
    });
  }

  private loadRace(): void {
    if (this.raceId) {
      this.raceService.getById(this.raceId).subscribe({
        next: (race) => {
          this.form.patchValue({
            name: race.name,
            circuit_id: race.circuit_id,
            scheduled_at: race.scheduled_at ? race.scheduled_at.slice(0, 16) : '',
            total_laps: race.total_laps,
            notes: race.notes || ''
          });
          this.loadingData = false;
        },
        error: () => {
          this.error = 'Error al cargar la carrera.';
          this.loadingData = false;
        }
      });
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
      ? this.raceService.update(this.raceId!, data)
      : this.raceService.create(this.championshipId, data);

    request$.subscribe({
      next: () => {
        this.router.navigate(['/organizer/championships', this.championshipId, 'races']);
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al guardar la carrera.';
        this.loading = false;
      }
    });
  }

  isInvalid(field: string): boolean {
    const control = this.form.get(field);
    return !!(control && control.invalid && control.touched);
  }
}
