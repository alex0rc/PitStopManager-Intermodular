import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { ResultService } from '../../../../core/services/result.service';
import { Result } from '../../../../core/models/result.model';

export interface ChampionshipResultGroup {
  championshipId: number;
  championshipName: string;
  raceOptions: { id: number; name: string }[];
  results: Result[];
}

@Component({
  selector: 'app-pilot-result-list',
  standalone: true,
  imports: [RouterLink, FormsModule],
  templateUrl: './pilot-result-list.component.html',
  styleUrl: './pilot-result-list.component.scss',
})
export class PilotResultListComponent implements OnInit {
  results: Result[] = [];
  groups: ChampionshipResultGroup[] = [];
  selectedRaceByChampionship: Record<number, number | 'all'> = {};

  loading = true;
  error = '';

  constructor(private resultService: ResultService) {}

  ngOnInit(): void {
    this.loadResults();
  }

  loadResults(): void {
    this.loading = true;
    this.error = '';
    this.resultService.getMyResults().subscribe({
      next: (data) => {
        this.results = data;
        this.buildGroups();
        this.loading = false;
      },
      error: () => {
        this.error = 'No se pudieron cargar los resultados.';
        this.loading = false;
      },
    });
  }

  private buildGroups(): void {
    const map = new Map<number, ChampionshipResultGroup>();

    for (const result of this.results) {
      const championshipId = result.race?.championship_id ?? 0;
      const championshipName =
        result.race?.championship?.name ?? `Campeonato #${championshipId || '?'}`;

      if (!map.has(championshipId)) {
        map.set(championshipId, {
          championshipId,
          championshipName,
          raceOptions: [],
          results: [],
        });
        this.selectedRaceByChampionship[championshipId] = 'all';
      }

      const group = map.get(championshipId)!;
      group.results.push(result);

      const raceId = result.race_id;
      const raceName = result.race?.name ?? `Carrera #${raceId}`;
      if (!group.raceOptions.some((r) => r.id === raceId)) {
        group.raceOptions.push({ id: raceId, name: raceName });
      }
    }

    this.groups = [...map.values()]
      .filter((g) => g.championshipId !== 0 || g.results.length > 0)
      .sort((a, b) => a.championshipName.localeCompare(b.championshipName, 'es'));

    for (const group of this.groups) {
      group.raceOptions.sort((a, b) => a.name.localeCompare(b.name, 'es'));
    }
  }

  filteredResults(group: ChampionshipResultGroup): Result[] {
    const selected = this.selectedRaceByChampionship[group.championshipId] ?? 'all';
    if (selected === 'all') {
      return [...group.results].sort((a, b) => {
        const da = a.race?.scheduled_at ? new Date(a.race.scheduled_at).getTime() : 0;
        const db = b.race?.scheduled_at ? new Date(b.race.scheduled_at).getTime() : 0;
        return db - da;
      });
    }
    return group.results.filter((r) => r.race_id === selected);
  }

  groupPoints(group: ChampionshipResultGroup): number {
    return this.filteredResults(group).reduce((sum, r) => sum + r.points, 0);
  }

  groupPodiums(group: ChampionshipResultGroup): number {
    return this.filteredResults(group).filter(
      (r) => r.position !== null && r.position <= 3 && !r.dsq && !r.dnf,
    ).length;
  }

  positionLabel(result: Result): string {
    if (result.dsq) return 'DSQ';
    if (result.dnf) return 'DNF';
    if (result.position) return `${result.position}º`;
    return '—';
  }

  positionClass(result: Result): string {
    if (result.dsq) return 'text-danger fw-bold';
    if (result.dnf) return 'text-warning fw-bold';
    if (result.position === 1) return 'text-warning fw-bold';
    if (result.position && result.position <= 3) return 'fw-bold';
    return '';
  }

  get totalPoints(): number {
    return this.results.reduce((sum, r) => sum + r.points, 0);
  }

  get podiums(): number {
    return this.results.filter(
      (r) => r.position !== null && r.position <= 3 && !r.dsq && !r.dnf,
    ).length;
  }
}
