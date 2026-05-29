import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { SubscriptionService } from '../../../core/services/subscription.service';
import { Payment } from '../../../core/models/payment.model';

@Component({
  selector: 'app-pilot-payments',
  standalone: true,
  imports: [RouterLink, CurrencyPipe, DatePipe],
  templateUrl: './pilot-payments.component.html',
  styleUrl: './pilot-payments.component.scss',
})
export class PilotPaymentsComponent implements OnInit {
  payments: Payment[] = [];
  loading = true;
  error = '';

  constructor(private subscriptionService: SubscriptionService) {}

  ngOnInit(): void {
    this.subscriptionService.getMyPayments().subscribe({
      next: (payments) => {
        this.payments = payments;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.displayMessage || 'Error al cargar los pagos.';
        this.loading = false;
      },
    });
  }

  paymentStatusLabel(status: string): string {
    const labels: Record<string, string> = {
      succeeded: 'Completado',
      pending: 'Pendiente',
      failed: 'Fallido',
      refunded: 'Reembolsado',
    };
    return labels[status] ?? status;
  }

  getPaymentBadgeClass(status: string): string {
    const map: Record<string, string> = {
      succeeded: 'bg-success',
      pending: 'bg-warning text-dark',
      failed: 'bg-danger',
      refunded: 'bg-info',
    };
    return map[status] || 'bg-secondary';
  }

  downloadPdf(paymentId: number): void {
    this.subscriptionService.downloadPdf(paymentId).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `factura-${paymentId}.pdf`;
        a.click();
        window.URL.revokeObjectURL(url);
      },
      error: () => {
        this.error = 'Error al descargar el PDF.';
      },
    });
  }
}
