import { Component, inject } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { NotificationService } from '../../../core/services/notification.service';
import { adminPanelLoginUrl } from '../../../core/utils/admin-panel-url';
import { BrandLogoComponent } from '../../../shared/brand-logo/brand-logo.component';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, BrandLogoComponent],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent {
  form: FormGroup;
  loading = false;
  error = '';
  showPassword = false;
  year = new Date().getFullYear();
  private notifications = inject(NotificationService);

  private route = inject(ActivatedRoute);
  private returnUrl = this.route.snapshot.queryParamMap.get('returnUrl');

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    const sessionMsg = this.route.snapshot.queryParamMap.get('message');
    if (sessionMsg) {
      this.error = sessionMsg;
    }
    this.form = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required]]
    });
  }

  onSubmit(): void {
    if (this.form.invalid) return;

    this.loading = true;
    this.error = '';

    this.authService.login(this.form.value).subscribe({
      next: (res) => {
        this.loading = false;
        this.notifications.success(`Bienvenido de vuelta, ${res.user.name}.`);
        this.navigateByRole(res.user.role);
      },
      error: (err) => {
        this.loading = false;
        this.error = err.displayMessage || err.error?.message || 'Credenciales incorrectas. Inténtalo de nuevo.';
      }
    });
  }

  private navigateByRole(role: string): void {
    if (this.returnUrl && this.returnUrl.startsWith('/') && !this.returnUrl.startsWith('//')) {
      this.router.navigateByUrl(this.returnUrl);
      return;
    }

    switch (role) {
      case 'admin':
        window.location.href = adminPanelLoginUrl();
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
