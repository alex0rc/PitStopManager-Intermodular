import { Component, inject } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { NotificationService } from '../../../core/services/notification.service';
import { adminPanelLoginUrl } from '../../../core/utils/admin-panel-url';
import { BrandLogoComponent } from '../../../shared/brand-logo/brand-logo.component';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, BrandLogoComponent],
  templateUrl: './register.component.html',
  styleUrl: './register.component.scss'
})
export class RegisterComponent {
  form: FormGroup;
  loading = false;
  error = '';
  showPassword = false;
  showConfirm = false;
  year = new Date().getFullYear();
  private notifications = inject(NotificationService);
  private returnUrl = inject(ActivatedRoute).snapshot.queryParamMap.get('returnUrl');

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.form = this.fb.group({
      name: ['', [Validators.required]],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', [Validators.required]],
    }, { validators: this.passwordMatchValidator });
  }

  private passwordMatchValidator(control: AbstractControl): ValidationErrors | null {
    const password = control.get('password');
    const confirm = control.get('password_confirmation');
    if (password && confirm && password.value !== confirm.value) {
      confirm.setErrors({ passwordMismatch: true });
      return { passwordMismatch: true };
    }
    return null;
  }

  onSubmit(): void {
    if (this.form.invalid) return;

    this.loading = true;
    this.error = '';

    this.authService.register(this.form.value).subscribe({
      next: (res) => {
        this.loading = false;
        this.notifications.success(
          `¡Cuenta creada, ${res.user.name}! Bienvenido a la parrilla.`,
          'Registro completado'
        );
        this.navigateByRole(res.user.role);
      },
      error: (err) => {
        this.loading = false;
        this.error = err.displayMessage || err.error?.message || 'No se pudo completar el registro. Inténtalo de nuevo.';
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
