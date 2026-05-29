import { environment } from '../../../environments/environment';

/** URL base del panel Blade (mismo origen en Docker: /admin). */
export function adminPanelBaseUrl(): string {
  const base = environment.adminUrl.replace(/\/$/, '');
  return base.startsWith('http') ? base : `${window.location.origin}${base}`;
}

export function adminPanelLoginUrl(): string {
  return `${adminPanelBaseUrl()}/login`;
}
