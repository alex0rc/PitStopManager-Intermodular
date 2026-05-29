import { Component, OnInit, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { CircuitService } from '../../../../core/services/circuit.service';
import { AuthService } from '../../../../core/services/auth.service';
import { NotificationService } from '../../../../core/services/notification.service';
import { Circuit } from '../../../../core/models/circuit.model';
import { statusBadgeClass, statusLabel } from '../../../../core/utils/status.util';

@Component({
  selector: 'app-circuit-list',
  standalone: true,
  imports: [RouterLink, FormsModule],
  templateUrl: './circuit-list.component.html',
  styleUrls: ['./circuit-list.component.scss'],
})
export class CircuitListComponent implements OnInit {
  private circuitService = inject(CircuitService);
  private auth = inject(AuthService);
  private notifications = inject(NotificationService);

  activeTab: 'catalog' | 'mine' = 'catalog';

  catalogCircuits: Circuit[] = [];
  myCircuits: Circuit[] = [];
  provinces: string[] = [];
  filterProvince = '';

  loading = true;
  error = '';
  catalogPage = 1;
  catalogLastPage = 1;
  myPage = 1;
  myLastPage = 1;

  ngOnInit(): void {
    this.circuitService.getProvinces().subscribe({
      next: (list) => (this.provinces = list),
      error: () => {},
    });
    this.loadCatalog();
  }

  setTab(tab: 'catalog' | 'mine'): void {
    this.activeTab = tab;
    if (tab === 'mine') {
      this.loadMine();
    }
  }

  loadCatalog(): void {
    this.loading = true;
    this.error = '';
    const params: Record<string, string | number> = { page: this.catalogPage, per_page: 50 };
    if (this.filterProvince) params['province'] = this.filterProvince;

    this.circuitService.getAll(params).subscribe({
      next: (res) => {
        this.catalogCircuits = res.data;
        this.catalogPage = res.meta.current_page;
        this.catalogLastPage = res.meta.last_page;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.displayMessage || 'Error al cargar el catálogo.';
        this.loading = false;
      },
    });
  }

  loadMine(): void {
    this.loading = true;
    this.error = '';
    this.circuitService.getMine({ page: this.myPage, per_page: 20 }).subscribe({
      next: (res) => {
        this.myCircuits = res.data;
        this.myPage = res.meta.current_page;
        this.myLastPage = res.meta.last_page;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.displayMessage || 'Error al cargar tus propuestas.';
        this.loading = false;
      },
    });
  }

  applyProvinceFilter(): void {
    this.catalogPage = 1;
    this.loadCatalog();
  }

  isOwner(circuit: Circuit): boolean {
    return circuit.user_id === this.auth.currentUser?.id;
  }

  canEdit(circuit: Circuit): boolean {
    return this.isOwner(circuit) && circuit.status !== 'approved';
  }

  statusLabel = statusLabel;
  statusBadge = statusBadgeClass;

  onUploadImage(event: Event, circuit: Circuit): void {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    this.circuitService.uploadImage(circuit.id, input.files[0]).subscribe({
      next: (updated) => {
        const idx = this.myCircuits.findIndex((c) => c.id === updated.id);
        if (idx !== -1) this.myCircuits[idx] = updated;
        this.notifications.success('Imagen actualizada.');
      },
      error: (err) => {
        this.notifications.error(err.displayMessage || 'Error al subir la imagen.');
      },
    });
  }

  deleteCircuit(id: number): void {
    if (!confirm('¿Eliminar este circuito?')) return;
    this.circuitService.delete(id).subscribe({
      next: () => {
        this.myCircuits = this.myCircuits.filter((c) => c.id !== id);
        this.notifications.success('Circuito eliminado.');
      },
      error: (err) => {
        this.notifications.error(err.displayMessage || 'No puedes eliminar este circuito.');
      },
    });
  }
}
