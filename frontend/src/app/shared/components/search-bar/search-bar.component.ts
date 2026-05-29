import { Component, input, output, OnInit, OnDestroy } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Subject, Subscription, debounceTime, distinctUntilChanged } from 'rxjs';

@Component({
  selector: 'app-search-bar',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="input-group">
      <span class="input-group-text">
        <i class="bi bi-search"></i>
      </span>
      <input
        type="text"
        class="form-control"
        [placeholder]="placeholder()"
        [ngModel]="searchTerm"
        (ngModelChange)="onInput($event)"
      />
    </div>
  `,
})
export class SearchBarComponent implements OnInit, OnDestroy {
  placeholder = input<string>('Buscar...');
  searchChange = output<string>();

  searchTerm = '';
  private searchSubject = new Subject<string>();
  private subscription!: Subscription;

  ngOnInit(): void {
    this.subscription = this.searchSubject
      .pipe(debounceTime(300), distinctUntilChanged())
      .subscribe((term) => this.searchChange.emit(term));
  }

  onInput(value: string): void {
    this.searchTerm = value;
    this.searchSubject.next(value);
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }
}
