import { Component, OnInit, inject } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';

export interface ErrorPageConfig {
  code: number;
  title: string;
  message: string;
  icon: string;
}

const PRESETS: Record<string, ErrorPageConfig> = {
  '404': {
    code: 404,
    title: 'Página no encontrada',
    message: 'La ruta que buscas no existe o el campeonato ya no está disponible.',
    icon: 'bi-signpost-split',
  },
  '403': {
    code: 403,
    title: 'Acceso denegado',
    message: 'Tu cuenta no tiene permiso para ver esta sección. Inicia sesión con el rol correcto.',
    icon: 'bi-shield-lock',
  },
  '401': {
    code: 401,
    title: 'Sesión requerida',
    message: 'Debes iniciar sesión para acceder a esta página.',
    icon: 'bi-person-lock',
  },
  '500': {
    code: 500,
    title: 'Error del servidor',
    message: 'Algo falló en nuestro lado. Inténtalo de nuevo en unos minutos.',
    icon: 'bi-exclamation-octagon',
  },
};

@Component({
  selector: 'app-error-page',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './error-page.component.html',
  styleUrl: './error-page.component.scss',
})
export class ErrorPageComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private auth = inject(AuthService);

  config: ErrorPageConfig = PRESETS['404'];
  homeLink = '/';

  ngOnInit(): void {
    const code = this.route.snapshot.paramMap.get('code') ?? '404';
    this.config = PRESETS[code] ?? PRESETS['404'];

    const customMessage = this.route.snapshot.queryParamMap.get('message');
    if (customMessage) {
      this.config = { ...this.config, message: customMessage };
    }

    const user = this.auth.currentUser;
    if (user) {
      this.homeLink =
        user.role === 'organizer'
          ? '/organizer/championships'
          : user.role === 'pilot'
            ? '/pilot'
            : user.role === 'admin'
              ? '/admin'
              : '/';
    }
  }
}
