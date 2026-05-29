import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { SubscriptionService } from '../../../../core/services/subscription.service';
import { Subscription, SubscriptionPlan, SubscriptionQuota } from '../../../../core/models/subscription.model';
import { Payment } from '../../../../core/models/payment.model';

@Component({
  selector: 'app-subscription-page',
  standalone: true,
  imports: [CurrencyPipe, DatePipe],
  templateUrl: './subscription-page.component.html',
  styleUrls: ['./subscription-page.component.scss']
})
export class SubscriptionPageComponent implements OnInit {
  subscription?: Subscription;
  quota?: SubscriptionQuota;
  plans: SubscriptionPlan[] = [];
  payments: Payment[] = [];
  loading = true;
  subscribing = false;
  error = '';
  successMessage = '';

  constructor(
    private subscriptionService: SubscriptionService,
    private route: ActivatedRoute,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.handleQueryParams();
    this.loadData();
  }

  private handleQueryParams(): void {
    const url = this.router.url;
    const sessionId = this.route.snapshot.queryParamMap.get('session_id');

    if (url.includes('/subscription/success') || sessionId) {
      this.successMessage = '¡Suscripción realizada con éxito! Tu pago ha sido procesado.';
    } else if (url.includes('/subscription/cancel')) {
      this.error = 'El proceso de pago fue cancelado.';
    }
  }

  loadData(): void {
    this.loading = true;

    this.subscriptionService.getMySubscriptionWithQuota().subscribe({
      next: ({ subscription, quota }) => {
        this.subscription = subscription ?? undefined;
        this.quota = quota;
        this.loadPlansAndPayments();
      },
      error: () => {
        this.subscription = undefined;
        this.quota = undefined;
        this.loadPlansAndPayments();
      },
    });
  }

  private loadPlansAndPayments(): void {
    this.subscriptionService.getPlans().subscribe({
      next: (plans) => {
        this.plans = plans.filter(p => p.is_active);
        this.loadPayments();
      },
      error: () => {
        this.error = this.error || 'Error al cargar los planes.';
        this.loading = false;
      }
    });
  }

  private loadPayments(): void {
    this.subscriptionService.getMyPayments().subscribe({
      next: (payments) => {
        this.payments = payments;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    });
  }

  get isActive(): boolean {
    return this.subscription?.status === 'active';
  }

  get isExpired(): boolean {
    return this.subscription?.status === 'expired';
  }

  getStatusBadgeClass(status: string): string {
    const map: Record<string, string> = {
      active: 'bg-success',
      expired: 'bg-warning text-dark',
      cancelled: 'bg-danger',
      pending: 'bg-info'
    };
    return map[status] || 'bg-secondary';
  }

  getPaymentBadgeClass(status: string): string {
    const map: Record<string, string> = {
      succeeded: 'bg-success',
      pending: 'bg-warning text-dark',
      failed: 'bg-danger',
      refunded: 'bg-info'
    };
    return map[status] || 'bg-secondary';
  }

  subscribe(planId: number): void {
    this.subscribing = true;
    this.error = '';
    this.subscriptionService.subscribe(planId).subscribe({
      next: (result) => {
        if (result.checkout_url) {
          window.location.href = result.checkout_url;
        } else {
          this.successMessage = '¡Suscripción creada con éxito!';
          this.subscribing = false;
          this.loadData();
        }
      },
      error: (err) => {
        this.error = err.displayMessage || err.error?.message || 'Error al procesar la suscripción.';
        this.subscribing = false;
      }
    });
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
      }
    });
  }
}
