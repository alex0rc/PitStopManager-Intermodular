import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { AuthService } from '../services/auth.service';
import { NotificationService } from '../services/notification.service';
import { extractApiErrorMessage } from '../utils/http-error-messages';

let isRedirecting = false;

function shouldToast(error: HttpErrorResponse, url: string): boolean {
  if (url.includes('/api/login') || url.includes('/api/register')) {
    return false;
  }
  if (error.status === 401) {
    return false;
  }
  if (error.status === 0) {
    return true;
  }
  if (error.status >= 500) {
    return true;
  }
  if (error.status === 403) {
    return true;
  }
  if (error.status === 404) {
    return false;
  }
  if (error.status === 422) {
    return false;
  }
  return true;
}

export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);
  const authService = inject(AuthService);
  const notifications = inject(NotificationService);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      const summary = extractApiErrorMessage(error);

      if (error.status === 401 && !isRedirecting) {
        const isAuthRequest =
          req.url.includes('/login') ||
          req.url.includes('/register') ||
          req.url.includes('/user');
        if (!isAuthRequest) {
          isRedirecting = true;
          authService.clearSession();
          const returnUrl = router.url.startsWith('/login') || router.url.startsWith('/register')
            ? undefined
            : router.url;
          router
            .navigate(['/login'], {
              queryParams: returnUrl ? { returnUrl, message: summary } : { message: summary },
            })
            .then(() => {
              isRedirecting = false;
            });
        }
      }

      if (
        error.status === 403 &&
        authService.isLoggedIn &&
        !req.url.includes('/login') &&
        summary.toLowerCase().includes('desactivad')
      ) {
        authService.clearSession();
        router.navigate(['/login'], { queryParams: { message: summary } });
      }

      if (shouldToast(error, req.url)) {
        notifications.error(summary);
      }

      (error as HttpErrorResponse & { displayMessage?: string }).displayMessage = summary;
      return throwError(() => error);
    }),
  );
};
