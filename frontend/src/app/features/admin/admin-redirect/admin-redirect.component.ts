import { Component, OnInit } from '@angular/core';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-admin-redirect',
  standalone: true,
  template: `<p class="text-center text-muted py-5">Redirigiendo al panel de administración…</p>`,
})
export class AdminRedirectComponent implements OnInit {
  ngOnInit(): void {
    const url = environment.adminUrl.replace(/\/$/, '');
    window.location.href = url.startsWith('http') ? url : `${window.location.origin}${url}`;
  }
}
