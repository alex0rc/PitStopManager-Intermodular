import { Injectable, signal } from '@angular/core';

export type ToastVariant = 'success' | 'error' | 'info' | 'warning';

export interface Toast {
  id: number;
  message: string;
  title?: string;
  variant: ToastVariant;
  timeout: number;
}

@Injectable({ providedIn: 'root' })
export class NotificationService {
  private nextId = 1;
  readonly toasts = signal<Toast[]>([]);

  show(message: string, opts: { variant?: ToastVariant; title?: string; timeout?: number } = {}): number {
    const toast: Toast = {
      id: this.nextId++,
      message,
      title: opts.title,
      variant: opts.variant ?? 'info',
      timeout: opts.timeout ?? 4500,
    };

    this.toasts.update((list) => [...list, toast]);

    if (toast.timeout > 0) {
      setTimeout(() => this.dismiss(toast.id), toast.timeout);
    }

    return toast.id;
  }

  success(message: string, title?: string): number {
    return this.show(message, { variant: 'success', title });
  }

  error(message: string, title?: string): number {
    return this.show(message, { variant: 'error', title, timeout: 6500 });
  }

  warning(message: string, title?: string): number {
    return this.show(message, { variant: 'warning', title });
  }

  info(message: string, title?: string): number {
    return this.show(message, { variant: 'info', title });
  }

  dismiss(id: number): void {
    this.toasts.update((list) => list.filter((t) => t.id !== id));
  }

  clear(): void {
    this.toasts.set([]);
  }
}
