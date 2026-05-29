import { Component, OnInit, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { CurrencyPipe } from '@angular/common';
import { SubscriptionService } from '../../../core/services/subscription.service';
import { NotificationService } from '../../../core/services/notification.service';
import { SubscriptionPlan } from '../../../core/models/subscription.model';

@Component({
  selector: 'app-pilot-upgrade',
  standalone: true,
  imports: [RouterLink, CurrencyPipe],
  templateUrl: './pilot-upgrade.component.html',
  styleUrl: './pilot-upgrade.component.scss',
})
export class PilotUpgradeComponent implements OnInit {
  private subscriptionService = inject(SubscriptionService);
  private notifications = inject(NotificationService);

  plans: SubscriptionPlan[] = [];
  loading = true;
  subscribing: number | null = null;

  ngOnInit(): void {
    this.subscriptionService.getPlans().subscribe({
      next: (plans) => {
        this.plans = (plans ?? []).filter((p) => p.is_active);
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      },
    });
  }

  pricePerMonth(plan: SubscriptionPlan): number {
    const months = Math.max(1, Math.round(plan.duration_days / 30));
    return Math.round((plan.price / months) * 100) / 100;
  }

  isPopular(plan: SubscriptionPlan, index: number): boolean {
    if (this.plans.length === 1) return true;
    return index === 1 || this.plans.length === 2 && index === 0;
  }

  subscribe(plan: SubscriptionPlan): void {
    this.subscribing = plan.id;
    this.subscriptionService.subscribe(plan.id).subscribe({
      next: (result) => {
        if (result.checkout_url) {
          window.location.href = result.checkout_url;
        } else {
          this.notifications.success('Suscripción creada.');
          this.subscribing = null;
        }
      },
      error: () => {
        this.subscribing = null;
      },
    });
  }
}
