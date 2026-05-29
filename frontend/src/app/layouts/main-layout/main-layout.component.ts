import { Component, HostBinding, HostListener, OnDestroy, OnInit, inject } from '@angular/core';
import { NavigationEnd, Router, RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { AsyncPipe, NgTemplateOutlet } from '@angular/common';
import { filter, Subscription } from 'rxjs';
import { AuthService } from '../../core/services/auth.service';
import { ThemeService } from '../../core/services/theme.service';
import { environment } from '../../../environments/environment';
import { BrandLogoComponent } from '../../shared/brand-logo/brand-logo.component';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive, AsyncPipe, NgTemplateOutlet, BrandLogoComponent],
  templateUrl: './main-layout.component.html',
  styleUrl: './main-layout.component.scss',
})
export class MainLayoutComponent implements OnInit, OnDestroy {
  private authService = inject(AuthService);
  private router = inject(Router);
  theme = inject(ThemeService);

  currentUser$ = this.authService.currentUser$;
  navbarCollapsed = true;
  userMenuOpen = false;
  year = new Date().getFullYear();
  readonly adminUrl = environment.adminUrl;
  isAuthRoute = false;

  private routerSub?: Subscription;
  private readonly authPaths = ['/login', '/register', '/auth/callback'];

  @HostBinding('class.layout-auth')
  get layoutAuthClass(): boolean {
    return this.isAuthRoute;
  }

  ngOnInit(): void {
    this.updateAuthRoute();
    this.routerSub = this.router.events
      .pipe(filter((e) => e instanceof NavigationEnd))
      .subscribe(() => {
        this.closeMenus();
        this.updateAuthRoute();
      });
  }

  private updateAuthRoute(): void {
    const path = this.router.url.split('?')[0];
    this.isAuthRoute = this.authPaths.some((p) => path === p || path.startsWith(p + '/'));
  }

  ngOnDestroy(): void {
    this.routerSub?.unsubscribe();
    document.body.classList.remove('nav-open', 'user-sheet-open');
  }

  toggleNavbar(): void {
    this.navbarCollapsed = !this.navbarCollapsed;
    if (!this.navbarCollapsed) {
      this.userMenuOpen = false;
      document.body.classList.add('nav-open');
    } else {
      document.body.classList.remove('nav-open');
    }
  }

  toggleUserMenu(event: Event): void {
    event.stopPropagation();
    this.userMenuOpen = !this.userMenuOpen;
    if (this.userMenuOpen) {
      this.navbarCollapsed = true;
      document.body.classList.remove('nav-open');
      document.body.classList.add('user-sheet-open');
    } else {
      document.body.classList.remove('user-sheet-open');
    }
  }

  closeMenus(): void {
    this.navbarCollapsed = true;
    this.userMenuOpen = false;
    document.body.classList.remove('nav-open');
    document.body.classList.remove('user-sheet-open');
  }

  roleLabel(role: string): string {
    const labels: Record<string, string> = {
      admin: 'Administrador',
      organizer: 'Organizador',
      pilot: 'Piloto',
    };
    return labels[role] ?? role;
  }

  initials(name: string | null | undefined): string {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/).slice(0, 2);
    return parts.map((p) => p[0]?.toUpperCase() ?? '').join('') || '?';
  }

  @HostListener('document:click', ['$event'])
  onDocumentClick(event: MouseEvent): void {
    if (!this.userMenuOpen) return;
    const target = event.target as HTMLElement | null;
    if (target?.closest('.user-menu') || target?.closest('.user-sheet')) return;
    this.closeMenus();
  }

  @HostListener('window:resize')
  onResize(): void {
    if (window.innerWidth >= 992 && !this.navbarCollapsed) {
      this.closeMenus();
    }
  }

  toggleTheme(): void {
    this.theme.toggle();
    this.userMenuOpen = false;
  }

  logout(): void {
    this.closeMenus();
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/login']),
      error: () => {
        this.authService.clearSession();
        this.router.navigate(['/login']);
      },
    });
  }
}
