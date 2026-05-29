export interface RaceResultEntry {
  user_id: number;
  pilot_name: string | null;
  position: number | null;
  best_lap_time: string | null;
  total_time: string | null;
  points: number;
  dnf: boolean;
  dsq: boolean;
}

export interface RaceWithResults {
  race_id: number;
  race_name: string;
  scheduled_at: string;
  status: string;
  circuit_name: string | null;
  results: RaceResultEntry[];
}
