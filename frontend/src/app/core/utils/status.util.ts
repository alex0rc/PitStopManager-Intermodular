// --- Estados ---
export const STATUS_LABELS: Record<string, string> = {
  pending: 'Pendiente',
  confirmed: 'Confirmada',
  rejected: 'Rechazado',
  withdrawn: 'Retirada',
  approved: 'Aprobado',
  draft: 'Borrador',
  published: 'Publicado',
  in_progress: 'En curso',
  finished: 'Finalizado',
  cancelled: 'Cancelado',
  scheduled: 'Programada',
  completed: 'Completada',
  paid: 'Pagado',
  failed: 'Fallido',
  expired: 'Expirada',
  active: 'Activa',
};

export function statusLabel(status: string | undefined | null): string {
  if (!status) return '—';
  return STATUS_LABELS[status] ?? status;
}

export function statusBadgeClass(status: string | undefined | null): string {
  const map: Record<string, string> = {
    pending: 'bg-warning',
    draft: 'bg-secondary',
    published: 'bg-info',
    in_progress: 'bg-warning',
    scheduled: 'bg-info',
    confirmed: 'bg-success',
    approved: 'bg-success',
    completed: 'bg-success',
    finished: 'bg-success',
    paid: 'bg-success',
    active: 'bg-success',
    rejected: 'bg-danger',
    cancelled: 'bg-danger',
    failed: 'bg-danger',
    withdrawn: 'bg-secondary',
    expired: 'bg-secondary',
  };
  return map[status ?? ''] ?? 'bg-secondary';
}
