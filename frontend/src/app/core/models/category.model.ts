export interface Category {
  id: number;
  name: string;
  description: string | null;
  min_age: number | null;
  max_age: number | null;
  max_weight_kg: number | null;
}
