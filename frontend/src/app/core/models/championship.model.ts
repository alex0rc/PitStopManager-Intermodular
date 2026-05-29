import { User } from './user.model';
import { Category } from './category.model';

export interface Championship {
  id: number;
  user_id: number;
  category_id: number;
  kart_modality?: 'rental' | 'own';
  engine_class?: string | null;
  name: string;
  description: string | null;
  image: string | null;
  venue_country?: string | null;
  venue_province?: string | null;
  venue_city?: string | null;
  venue_latitude?: number | null;
  venue_longitude?: number | null;
  season_year: number;
  status: 'draft' | 'published' | 'in_progress' | 'finished' | 'cancelled';
  start_date: string | null;
  end_date: string | null;
  user?: User;
  category?: Category;
  races_count?: number;
  created_at?: string;
}
