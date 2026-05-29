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
