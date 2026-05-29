import { Component, OnInit, inject } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { User } from '../../../core/models/user.model';
import { BrandLogoComponent } from '../../../shared/brand-logo/brand-logo.component';

@Component({
  selector: 'app-auth-callback',
  standalone: true,
  imports: [BrandLogoComponent],
  template: `
    <div class="d-flex flex-column align-items-center justify-content-center min-vh-100 p-4">
      <div class="mb-4"><app-brand-logo size="lg" /></div>
      <div class="spinner-border text-danger" role="status"></div>
      <p class="mt-3 text-muted">Conectando tu sesión…</p>
    </div>
  `,
})
export class AuthCallbackComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private auth = inject(AuthService);

  ngOnInit(): void {
    const token = this.route.snapshot.queryParamMap.get('token');

    if (!token) {
      this.router.navigate(['/login']);
      return;
    }

    this.auth.setTokenFromBridge(token).subscribe({
      next: (user: User) => this.navigateByRole(user.role),
      error: () => this.router.navigate(['/login']),
    });
  }

  private navigateByRole(role: string): void {
    switch (role) {
      case 'admin':
        this.router.navigate(['/']);
        break;
      case 'organizer':
        this.router.navigate(['/organizer/championships']);
        break;
      case 'pilot':
        this.router.navigate(['/pilot']);
        break;
      default:
        this.router.navigate(['/']);
    }
  }
}
