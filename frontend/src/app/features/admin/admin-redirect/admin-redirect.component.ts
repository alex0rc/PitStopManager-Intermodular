import { Component, OnInit } from '@angular/core';
import { adminPanelBaseUrl } from '../../../core/utils/admin-panel-url';

@Component({
  selector: 'app-admin-redirect',
  standalone: true,
  template: `<p class="text-center text-muted py-5">Redirigiendo al panel de administración…</p>`,
})
export class AdminRedirectComponent implements OnInit {
  ngOnInit(): void {
    window.location.href = adminPanelBaseUrl();
  }
}
