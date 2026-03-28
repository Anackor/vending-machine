import { afterEach, describe, expect, it, vi } from 'vitest';

import { MachineApiClient, MachineApiError } from './machineApiClient';

describe('MachineApiClient', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('loads the machine state through the reviewer API path using coin-based responses', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          machine: {
            machineId: 'default',
            insertedBalanceCoins: 0.25,
            hasPendingBalance: true,
            insertedCoins: { '0.25': 1 },
            availableChangeCounts: { '0.05': 20, '0.10': 20, '0.25': 20, '1': 10 },
            products: [
              {
                selector: 'water',
                name: 'Water',
                priceCoins: 0.65,
                quantity: 10,
                available: true,
              },
            ],
          },
        }),
        {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        },
      ),
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
    expect(result.data.machine.insertedBalanceCoins).toBe(0.25);
    expect(result.data.machine.insertedCoins).toEqual({ '0.25': 1 });
    expect(result.data.machine.availableChangeCounts).toEqual({
      '0.05': 20,
      '0.10': 20,
      '0.25': 20,
      '1': 10,
    });
    expect(result.data.machine.products[0]?.priceCoins).toBe(0.65);
    expect(result.exchange.responseBody).toEqual(result.data);
  });

  it('still normalizes legacy cent-based responses for compatibility', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          machine: {
            machineId: 'default',
            insertedBalanceCents: 25,
            hasPendingBalance: true,
            insertedCoins: { 25: 1 },
            availableChangeCounts: { 5: 20, 10: 20, 25: 20, 100: 10 },
            products: [
              {
                selector: 'water',
                name: 'Water',
                priceCents: 65,
                quantity: 10,
                available: true,
              },
            ],
          },
        }),
        {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        },
      ),
    );

    vi.stubGlobal('fetch', fetchMock);

    const client = new MachineApiClient();
    const result = await client.getMachineState();

    expect(result.data.machine.insertedBalanceCoins).toBe(0.25);
    expect(result.data.machine.insertedCoins).toEqual({ '0.25': 1 });
    expect(result.data.machine.products[0]?.priceCoins).toBe(0.65);
  });

  it('sends reviewer-friendly coin input when inserting a coin and keeps the exchange in coins', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          event: { type: 'coin_inserted', coins: 0.25 },
          machine: {
            machineId: 'default',
            insertedBalanceCoins: 0.25,
            hasPendingBalance: true,
            insertedCoins: { '0.25': 1 },
            availableChangeCounts: { '0.05': 20, '0.10': 20, '0.25': 20, '1': 10 },
            products: [],
          },
        }),
        {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        },
      ),
    );

    vi.stubGlobal('fetch', fetchMock);

    const client = new MachineApiClient();
    const result = await client.insertCoin(0.25);

    expect(fetchMock).toHaveBeenCalledWith('/api/machine/insert-coin', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ coins: 0.25 }),
    });
    expect(result.data.event.coins).toBe(0.25);
    expect(result.data.machine.insertedBalanceCoins).toBe(0.25);
    expect(result.exchange.requestBody).toEqual({ coins: 0.25 });
    expect(result.exchange.responseBody).toEqual(result.data);
  });

  it('normalizes select-product and return-coin responses to reviewer-facing coin maps', async () => {
    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({
            event: {
              type: 'product_selected',
              dispensedProduct: { name: 'Water', selector: 'water' },
              dispensedChangeCounts: { '0.10': 1, '0.25': 1 },
            },
            machine: {
              machineId: 'default',
              insertedBalanceCoins: 0,
              hasPendingBalance: false,
              insertedCoins: {},
              availableChangeCounts: { '0.05': 19, '0.10': 19, '0.25': 19, '1': 10 },
              products: [
                {
                  selector: 'water',
                  name: 'Water',
                  priceCoins: 0.65,
                  quantity: 9,
                  available: true,
                },
              ],
            },
          }),
          {
            status: 200,
            headers: { 'Content-Type': 'application/json' },
          },
        ),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({
            event: {
              type: 'money_returned',
              returnedCoinCounts: { '1': 1 },
            },
            machine: {
              machineId: 'default',
              insertedBalanceCoins: 0,
              hasPendingBalance: false,
              insertedCoins: {},
              availableChangeCounts: { '0.05': 20, '0.10': 20, '0.25': 20, '1': 10 },
              products: [],
            },
          }),
          {
            status: 200,
            headers: { 'Content-Type': 'application/json' },
          },
        ),
      );

    vi.stubGlobal('fetch', fetchMock);

    const client = new MachineApiClient();
    const selection = await client.selectProduct('water');
    const refund = await client.returnInsertedMoney();

    expect(selection.data.event.dispensedChangeCounts).toEqual({
      '0.10': 1,
      '0.25': 1,
    });
    expect(selection.exchange.responseBody).toEqual(selection.data);
    expect(refund.data.event.returnedCoinCounts).toEqual({ '1': 1 });
    expect(refund.exchange.responseBody).toEqual(refund.data);
  });

  it('sends and receives service payloads in coin format', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          event: { type: 'machine_serviced' },
          machine: {
            machineId: 'default',
            insertedBalanceCoins: 0,
            hasPendingBalance: false,
            insertedCoins: {},
            availableChangeCounts: { '0.05': 10, '0.10': 10, '0.25': 10, '1': 10 },
            products: [],
          },
        }),
        {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        },
      ),
    );

    vi.stubGlobal('fetch', fetchMock);

    const client = new MachineApiClient();
    const result = await client.serviceMachine({
      productQuantities: { water: 10, juice: 8, soda: 5 },
      availableChangeCounts: {
        '0.05': 20,
        '0.10': 20,
        '0.25': 20,
        '1': 10,
      },
    });

    expect(fetchMock).toHaveBeenCalledWith('/api/machine/service', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        productQuantities: { water: 10, juice: 8, soda: 5 },
        availableChangeCounts: {
          '0.05': 20,
          '0.10': 20,
          '0.25': 20,
          '1': 10,
        },
      }),
    });
    expect(result.exchange.requestBody).toEqual({
      productQuantities: { water: 10, juice: 8, soda: 5 },
      availableChangeCounts: {
        '0.05': 20,
        '0.10': 20,
        '0.25': 20,
        '1': 10,
      },
    });
    expect(result.data.machine.availableChangeCounts).toEqual({
      '0.05': 10,
      '0.10': 10,
      '0.25': 10,
      '1': 10,
    });
    expect(result.exchange.responseBody).toEqual(result.data);
  });

  it('exposes normalized API failures together with the recorded exchange', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(
          JSON.stringify({
            error: {
              code: 'exact_change_unavailable',
              message: 'Exact change "0.50" cannot be returned for selector "water".',
              context: { selector: 'water' },
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

    await expect(client.selectProduct('water')).rejects.toEqual(
      expect.objectContaining<Partial<MachineApiError>>({
        code: 'exact_change_unavailable',
        message: 'Exact change "0.50" cannot be returned for selector "water".',
        exchange: expect.objectContaining({
          method: 'POST',
          path: '/api/machine/select-product',
          status: 409,
          responseBody: {
            error: {
              code: 'exact_change_unavailable',
              message: 'Exact change "0.50" cannot be returned for selector "water".',
              context: { selector: 'water' },
            },
          },
        }),
      }),
    );
  });
});
