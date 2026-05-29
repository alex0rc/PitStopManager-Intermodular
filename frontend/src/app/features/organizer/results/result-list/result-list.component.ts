import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { ResultService } from '../../../../core/services/result.service';
import { Result } from '../../../../core/models/result.model';

@Component({
  selector: 'app-result-list',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './result-list.component.html',
  styleUrls: ['./result-list.component.scss']
})
export class ResultListComponent implements OnInit {
  results: Result[] = [];
  raceId!: number;
  loading = true;
  error = '';

  constructor(
    private route: ActivatedRoute,
    private resultService: ResultService
  ) {}

  ngOnInit(): void {
    this.raceId = +this.route.snapshot.paramMap.get('raceId')!;
    this.loadResults();
  }

  loadResults(): void {
    this.loading = true;
    this.error = '';
    this.resultService.getByRace(this.raceId).subscribe({
      next: (results) => {
        this.results = results;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al cargar los resultados.';
        this.loading = false;
      }
    });
  }

  deleteResult(id: number): void {
    if (!confirm('¿Estás seguro de que deseas eliminar este resultado?')) return;
    this.resultService.delete(id).subscribe({
      next: () => {
        this.results = this.results.filter(r => r.id !== id);
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al eliminar el resultado.';
      }
    });
  }
}
