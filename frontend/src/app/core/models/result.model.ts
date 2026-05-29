import { User } from './user.model';
import { Race } from './race.model';

export interface Result {
  id: number;
  race_id: number;
  user_id: number;
  position: number | null;
  best_lap_time: string | null;
  total_time: string | null;
  points: number;
  dnf: boolean;
  dsq: boolean;
  notes: string | null;
  user?: User;
  race?: Race;
}
