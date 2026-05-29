import { Component, Input } from '@angular/core';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-brand-logo',
  standalone: true,
  host: { class: 'brand-logo-host' },
  template: `
    <img
      [src]="src"
      [alt]="alt"
      class="brand-logo"
      [class.brand-logo-sm]="size === 'sm'"
      [class.brand-logo-lg]="size === 'lg'"
      [width]="dims"
      [height]="dims"
      decoding="async"
    />
  `,
})
export class BrandLogoComponent {
  @Input() size: 'sm' | 'md' | 'lg' = 'md';
  @Input() alt = 'PitStop Manager';
  @Input() src = environment.logoUrl;

  get dims(): number {
    return { sm: 26, md: 34, lg: 52 }[this.size];
  }
}
