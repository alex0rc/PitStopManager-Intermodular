import { Component, input, output } from '@angular/core';

@Component({
  selector: 'app-pagination',
  standalone: true,
  imports: [],
  templateUrl: './pagination.component.html',
})
export class PaginationComponent {
  currentPage = input.required<number>();
  lastPage = input.required<number>();
  total = input.required<number>();

  pageChange = output<number>();

  goToPage(page: number): void {
    if (page >= 1 && page <= this.lastPage() && page !== this.currentPage()) {
      this.pageChange.emit(page);
    }
  }
}
