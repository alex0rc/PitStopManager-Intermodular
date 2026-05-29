import { inject } from '@angular/core';
import { Router, CanActivateFn, ActivatedRouteSnapshot } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const roleGuard: CanActivateFn = (route: ActivatedRouteSnapshot) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  let expectedRoles: string[] | undefined = route.data['roles'] as string[];

  if (!expectedRoles?.length) {
    let current: ActivatedRouteSnapshot | null = route.parent;
    while (current) {
      if (current.data['roles']) {
        expectedRoles = current.data['roles'] as string[];
        break;
      }
      current = current.parent;
    }
  }

  if (!expectedRoles?.length) {
    return true;
  }

  if (!authService.isLoggedIn) {
    return router.createUrlTree(['/login'], {
      queryParams: { returnUrl: router.url },
    });
  }

  const role = authService.currentUser?.role ?? '';
  if (expectedRoles.includes(role)) {
    return true;
  }

  const home = roleHome(role);
  if (home) {
    return router.createUrlTree([home]);
  }

  return router.createUrlTree(['/error', '403'], {
    queryParams: {
      message: `Tu cuenta es de tipo «${roleLabel(role)}» y no puede acceder a esta zona.`,
    },
  });
};

function roleHome(role: string): string | null {
  const map: Record<string, string> = {
    pilot: '/pilot',
    organizer: '/organizer/championships',
    admin: '/admin',
  };
  return map[role] ?? null;
}

function roleLabel(role: string): string {
  const labels: Record<string, string> = {
    admin: 'administrador',
    organizer: 'organizador',
    pilot: 'piloto',
  };
  return labels[role] ?? role;
}
