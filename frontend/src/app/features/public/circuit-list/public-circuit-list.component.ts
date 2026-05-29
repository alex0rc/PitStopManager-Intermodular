import { Component, OnInit, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { CircuitService } from '../../../core/services/circuit.service';
import { Circuit } from '../../../core/models/circuit.model';

@Component({
  selector: 'app-public-circuit-list',
  standalone: true,
  imports: [RouterLink, FormsModule],
  templateUrl: './public-circuit-list.component.html',
  styleUrl: './public-circuit-list.component.scss',
})
export class PublicCircuitListComponent implements OnInit {
  private circuitService = inject(CircuitService);

  circuits: Circuit[] = [];
  provinces: string[] = [];
  filterProvince = '';
  loading = true;
  error = '';

  ngOnInit(): void {
    this.circuitService.getProvinces().subscribe({
      next: (list) => (this.provinces = list),
      error: () => {},
    });
    this.load();
  }

  load(): void {
    this.loading = true;
    this.error = '';
    const params: Record<string, string | number> = { per_page: 60 };
    if (this.filterProvince) params['province'] = this.filterProvince;

    this.circuitService.getAll(params).subscribe({
      next: (res) => {
        this.circuits = res.data;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.displayMessage || 'No se pudo cargar el catálogo de circuitos.';
        this.loading = false;
      },
    });
  }

  applyFilter(): void {
    this.load();
  }
}
