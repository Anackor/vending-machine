import type { ApiExchange } from '../api/contracts';

/**
 * Keeps small presentation helpers centralized so the main app logic stays readable.
 */
export function formatCents(cents: number): string {
  return `€${(cents / 100).toFixed(2)}`;
}

export function formatCoins(value: number): string {
  return value % 1 === 0 ? value.toFixed(0) : value.toFixed(2);
}

export function formatCounts(counts: Record<string, number>): string {
  const entries = Object.entries(counts).filter(([, quantity]) => quantity > 0);

  if (entries.length === 0) {
    return 'none';
  }

  return entries.map(([coin, quantity]) => `${coin}c × ${quantity}`).join(', ');
}

export function prettyJson(value: unknown): string {
  return JSON.stringify(value, null, 2);
}

export function formatExchangeMeta(exchange: ApiExchange): string {
  return `${exchange.method} ${exchange.path} · ${exchange.status} · ${new Date(exchange.occurredAt).toLocaleTimeString()}`;
}
