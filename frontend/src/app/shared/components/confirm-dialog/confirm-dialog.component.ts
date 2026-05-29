import { Component, input, output } from '@angular/core';

@Component({
  selector: 'app-confirm-dialog',
  standalone: true,
  imports: [],
  templateUrl: './confirm-dialog.component.html',
  styleUrl: './confirm-dialog.component.scss',
})
export class ConfirmDialogComponent {
  message = input<string>('¿Estás seguro de que quieres continuar?');
  visible = false;

  confirmed = output<void>();
  cancelled = output<void>();

  open(): void {
    this.visible = true;
  }

  close(): void {
    this.visible = false;
  }

  onConfirm(): void {
    this.confirmed.emit();
    this.close();
  }

  onCancel(): void {
    this.cancelled.emit();
    this.close();
  }
}
