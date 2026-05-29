import { Pipe, PipeTransform } from '@angular/core';

const STATUS_CLASSES: Record<string, string> = {
  draft: 'bg-secondary',
  published: 'bg-success',
  in_progress: 'bg-primary',
  finished: 'bg-dark',
  cancelled: 'bg-danger',
  active: 'bg-success',
  expired: 'bg-warning',
  pending: 'bg-info',
  confirmed: 'bg-success',
  rejected: 'bg-danger',
  withdrawn: 'bg-secondary',
  succeeded: 'bg-success',
  failed: 'bg-danger',
};

@Pipe({
  name: 'statusBadge',
  standalone: true,
})
export class StatusBadgePipe implements PipeTransform {
  transform(status: string): string {
    return STATUS_CLASSES[status] ?? 'bg-secondary';
  }
}
