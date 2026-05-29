import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

export type ThemeMode = 'light' | 'dark';

@Injectable({ providedIn: 'root' })
export class ThemeService {
  private readonly storageKey = 'ps-theme';
  private themeSubject = new BehaviorSubject<ThemeMode>(this.readStored());
  theme$ = this.themeSubject.asObservable();

  constructor() {
    this.apply(this.themeSubject.value);
  }

  get current(): ThemeMode {
    return this.themeSubject.value;
  }

  get isDark(): boolean {
    return this.current === 'dark';
  }

  toggle(): void {
    this.set(this.isDark ? 'light' : 'dark');
  }

  set(mode: ThemeMode): void {
    this.themeSubject.next(mode);
    localStorage.setItem(this.storageKey, mode);
    this.apply(mode);
  }

  private readStored(): ThemeMode {
    const stored = localStorage.getItem(this.storageKey);
    return stored === 'dark' ? 'dark' : 'light';
  }

  private apply(mode: ThemeMode): void {
    document.documentElement.setAttribute('data-theme', mode);
  }
}
