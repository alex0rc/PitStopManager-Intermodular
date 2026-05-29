import { Championship } from './championship.model';
import { Circuit } from './circuit.model';

export interface Race {
  id: number;
  championship_id: number;
  circuit_id: number;
  name: string;
  scheduled_at: string;
  total_laps: number | null;
  status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
  notes: string | null;
  championship?: Championship;
  circuit?: Circuit;
}
