import { Component, computed, inject } from '@angular/core';
import { NgClass } from '@angular/common';
import { NotificationService, Toast } from '../../core/services/notification.service';

@Component({
  selector: 'app-toast-host',
  standalone: true,
  imports: [NgClass],
  templateUrl: './toast-host.component.html',
  styleUrl: './toast-host.component.scss',
})
export class ToastHostComponent {
  private notifications = inject(NotificationService);

  readonly toasts = computed(() => this.notifications.toasts());

  iconFor(t: Toast): string {
    switch (t.variant) {
      case 'success': return 'bi-check-circle-fill';
      case 'error':   return 'bi-exclamation-triangle-fill';
      case 'warning': return 'bi-exclamation-circle-fill';
      default:        return 'bi-info-circle-fill';
    }
  }

  dismiss(id: number): void {
    this.notifications.dismiss(id);
  }
}
