export function unwrapList<T>(res: T[] | { data?: T[] } | null | undefined): T[] {
  if (!res) return [];
  if (Array.isArray(res)) return res;
  return res.data ?? [];
}

export function unwrapData<T>(res: T | { data?: T } | null | undefined): T | null {
  if (res == null) return null;
  if (typeof res === 'object' && 'data' in res && (res as { data?: T }).data !== undefined) {
    return (res as { data: T }).data;
  }
  return res as T;
}

export interface PaginatedMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

/** Normalizes Laravel paginated JSON (with or without extra wrapping). */
export function normalizePaginated<T>(
  res: T[] | { data?: T[] | { data?: T[] }; meta?: PaginatedMeta } | null | undefined,
): { data: T[]; meta: PaginatedMeta } {
  const emptyMeta: PaginatedMeta = {
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  };

  if (!res) {
    return { data: [], meta: emptyMeta };
  }

  if (Array.isArray(res)) {
    return {
      data: res,
      meta: { ...emptyMeta, total: res.length, per_page: res.length || 15 },
    };
  }

  let items = res.data;
  if (items && typeof items === 'object' && !Array.isArray(items) && 'data' in items) {
    items = (items as { data?: T[] }).data;
  }

  const list = Array.isArray(items) ? items : [];
  const meta = res.meta ?? emptyMeta;

  return {
    data: list,
    meta: {
      current_page: meta.current_page ?? 1,
      last_page: meta.last_page ?? 1,
      per_page: meta.per_page ?? 15,
      total: meta.total ?? list.length,
    },
  };
}
