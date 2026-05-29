import { HttpErrorResponse } from '@angular/common/http';

const DEFAULT_BY_STATUS: Record<number, string> = {
  400: 'La solicitud no es válida. Revisa los datos e inténtalo de nuevo.',
  401: 'Tu sesión ha expirado. Vuelve a iniciar sesión.',
  403: 'No tienes permiso para realizar esta acción.',
  404: 'No hemos encontrado el recurso solicitado.',
  408: 'La solicitud tardó demasiado. Inténtalo de nuevo.',
  422: 'Hay errores en el formulario. Revisa los campos marcados.',
  429: 'Demasiadas solicitudes. Espera un momento e inténtalo de nuevo.',
  500: 'Error interno del servidor. Inténtalo más tarde.',
  502: 'El servidor no está disponible temporalmente.',
  503: 'Servicio en mantenimiento. Vuelve en unos minutos.',
};

export function extractApiErrorMessage(error: HttpErrorResponse): string {
  if (error.status === 0) {
    return 'No se pudo contactar con el servidor. Comprueba que la API esté en marcha y tu conexión.';
  }

  const body = error.error;
  if (body && typeof body === 'object') {
    if (error.status === 422 && body.errors && typeof body.errors === 'object') {
      const flat = Object.values(body.errors as Record<string, string[]>).flat();
      if (flat.length) {
        return flat.join(' ');
      }
    }
    if (typeof body.message === 'string' && body.message.trim()) {
      return body.message;
    }
  }

  return DEFAULT_BY_STATUS[error.status] ?? `Error del servidor (${error.status}).`;
}
