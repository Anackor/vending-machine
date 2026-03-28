import { afterEach, describe, expect, it, vi } from 'vitest';

import { MachineApiClient, MachineApiError } from './machineApiClient';

describe('MachineApiClient', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('loads the machine state through the reviewer API path', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(JSON.stringify({ machine: { machineId: 'default', products: [] } }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
      }),
    );

    vi.stubGlobal('fetch', fetchMock);

    const client = new MachineApiClient();
    const result = await client.getMachineState();

    expect(fetchMock).toHaveBeenCalledWith('/api/machine', {
      method: 'GET',
      headers: undefined,
      body: undefined,
    });
    expect(result.data.machine.machineId).toBe('default');
    expect(result.exchange.path).toBe('/api/machine');
  });

  it('sends reviewer-friendly coin input instead of raw cents', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(JSON.stringify({ event: { type: 'coin_inserted', coins: 0.25 }, machine: {} }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
      }),
    );

    vi.stubGlobal('fetch', fetchMock);

    const client = new MachineApiClient();
    await client.insertCoin(0.25);

    expect(fetchMock).toHaveBeenCalledWith('/api/machine/insert-coin', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ coins: 0.25 }),
    });
  });

  it('exposes API failures together with the recorded exchange', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(
          JSON.stringify({
            error: {
              code: 'insufficient_balance',
              message: 'Balance is not enough.',
              context: { requiredBalanceCents: 100 },
            },
          }),
          {
            status: 409,
            headers: { 'Content-Type': 'application/json' },
          },
        ),
      ),
    );

    const client = new MachineApiClient();

    await expect(client.selectProduct('juice')).rejects.toEqual(
      expect.objectContaining<Partial<MachineApiError>>({
        code: 'insufficient_balance',
        message: 'Balance is not enough.',
        exchange: expect.objectContaining({
          method: 'POST',
          path: '/api/machine/select-product',
          status: 409,
        }),
      }),
    );
  });
});
