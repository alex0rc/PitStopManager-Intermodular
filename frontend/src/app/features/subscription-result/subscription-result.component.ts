import { Component, OnInit, inject } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { NotificationService } from '../../core/services/notification.service';
import { SubscriptionService } from '../../core/services/subscription.service';

@Component({
  selector: 'app-subscription-result',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './subscription-result.component.html',
  styleUrl: './subscription-result.component.scss',
})
export class SubscriptionResultComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private auth = inject(AuthService);
  private notifications = inject(NotificationService);
  private subscriptions = inject(SubscriptionService);

  mode: 'success' | 'cancel' = 'success';
  refreshing = true;
  sessionId: string | null = null;
  user = this.auth.currentUser;
  wasOrganizer = false;

  ngOnInit(): void {
    this.wasOrganizer = this.auth.currentUser?.role === 'organizer';
    this.mode = this.router.url.includes('/cancel') ? 'cancel' : 'success';
    this.sessionId = this.route.snapshot.queryParamMap.get('session_id');

    if (this.mode === 'success') {
      if (this.sessionId) {
        this.confirmAndRefresh();
      } else {
        this.notifications.warning(
          'Falta el identificador de la sesión de pago. Si tu rol no cambia, contacta con soporte.',
          'Sesión no encontrada'
        );
        this.refreshUser();
      }
    } else {
      this.notifications.warning('El pago fue cancelado. No se realizó ningún cargo.');
      this.refreshing = false;
    }
  }

  private confirmAndRefresh(): void {
    this.subscriptions.confirmCheckout(this.sessionId!).subscribe({
      next: () => {
        const title = this.wasOrganizer ? 'Plan renovado' : 'Suscripción activa';
        const msg = this.wasOrganizer
          ? 'Pago confirmado. Tu suscripción sigue activa.'
          : 'Pago confirmado. Ya puedes crear campeonatos como organizador.';
        this.notifications.success(msg, title);
        this.refreshUser();
      },
      error: (err) => {
        if (err.status === 202) {
          this.notifications.info('El pago sigue procesándose. Espera unos segundos y actualiza.');
        }
        this.refreshUser();
      },
    });
  }

  private refreshUser(): void {
    this.auth.getUser().subscribe({
      next: (user) => {
        this.user = user;
        this.refreshing = false;
        if (user.role === 'organizer') {
          setTimeout(() => this.router.navigate(['/organizer/subscription']), 2400);
        }
      },
      error: () => {
        this.refreshing = false;
      },
    });
  }

  goToDashboard(): void {
    if (this.user?.role === 'organizer') {
      this.router.navigate(['/organizer/subscription']);
    } else if (this.user?.role === 'pilot') {
      this.router.navigate(['/pilot']);
    } else {
      this.router.navigate(['/']);
    }
  }
}
