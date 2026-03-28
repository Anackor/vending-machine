import { describe, expect, it } from 'vitest';

import type {
  ApiOperationResult,
  GetMachineStateResponse,
  InsertCoinResponse,
  MachineClient,
  ReturnInsertedMoneyResponse,
  SelectProductResponse,
  ServiceMachinePayload,
  ServiceMachineResponse,
} from '../api/contracts';
import { ReviewerApp } from './reviewerApp';

const initialMachine = {
  machineId: 'default',
  insertedBalanceCents: 0,
  hasPendingBalance: false,
  insertedCoins: {},
  availableChangeCounts: { 5: 20, 10: 20, 25: 20, 100: 10 },
  products: [
    { selector: 'water', name: 'Water', priceCents: 65, quantity: 10, available: true },
    { selector: 'juice', name: 'Juice', priceCents: 100, quantity: 8, available: true },
    { selector: 'soda', name: 'Soda', priceCents: 150, quantity: 5, available: true },
  ],
};

class FakeMachineClient implements MachineClient {
  public servicePayloads: ServiceMachinePayload[] = [];

  public async getMachineState(): Promise<ApiOperationResult<GetMachineStateResponse>> {
    return {
      data: { machine: initialMachine },
      exchange: this.exchange('GET', '/api/machine', null, { machine: initialMachine }),
    };
  }

  public async insertCoin(coins: number): Promise<ApiOperationResult<InsertCoinResponse>> {
    return {
      data: {
        event: { type: 'coin_inserted', coins },
        machine: { ...initialMachine, insertedBalanceCents: coins === 1 ? 100 : 25 },
      },
      exchange: this.exchange('POST', '/api/machine/insert-coin', { coins }, {
        event: { type: 'coin_inserted', coins },
      }),
    };
  }

  public async selectProduct(
    selector: string,
  ): Promise<ApiOperationResult<SelectProductResponse>> {
    return {
      data: {
        event: {
          type: 'product_selected',
          dispensedProduct: { name: 'Water', selector },
          dispensedChangeCounts: { 25: 1, 10: 1 },
        },
        machine: {
          ...initialMachine,
          insertedBalanceCents: 0,
          products: initialMachine.products.map((product) =>
            product.selector === selector
              ? { ...product, quantity: product.quantity - 1 }
              : product,
          ),
        },
      },
      exchange: this.exchange('POST', '/api/machine/select-product', { selector }, {
        event: { type: 'product_selected' },
      }),
    };
  }

  public async returnInsertedMoney(): Promise<
    ApiOperationResult<ReturnInsertedMoneyResponse>
  > {
    return {
      data: {
        event: {
          type: 'money_returned',
          returnedCoinCounts: { 100: 1 },
        },
        machine: initialMachine,
      },
      exchange: this.exchange('POST', '/api/machine/return-coin', null, {
        event: { type: 'money_returned' },
      }),
    };
  }

  public async serviceMachine(
    payload: ServiceMachinePayload,
  ): Promise<ApiOperationResult<ServiceMachineResponse>> {
    this.servicePayloads.push(payload);

    return {
      data: {
        event: { type: 'machine_serviced' },
        machine: {
          ...initialMachine,
          availableChangeCounts: payload.availableChangeCounts,
          products: initialMachine.products.map((product) => ({
            ...product,
            quantity: payload.productQuantities[product.selector],
          })),
        },
      },
      exchange: this.exchange('POST', '/api/machine/service', payload, {
        event: { type: 'machine_serviced' },
      }),
    };
  }

  private exchange(
    method: string,
    path: string,
    requestBody: unknown,
    responseBody: unknown,
  ) {
    return {
      label: 'test',
      method,
      path,
      requestBody,
      responseBody,
      status: 200,
      occurredAt: '2026-03-28T00:00:00.000Z',
    };
  }
}

function flush(): Promise<void> {
  return new Promise((resolve) => {
    setTimeout(resolve, 0);
  });
}

describe('ReviewerApp', () => {
  it('renders the machine snapshot after the first load', async () => {
    const root = document.createElement('div');
    const app = new ReviewerApp(root, new FakeMachineClient());

    await app.mount();

    expect(root.textContent).toContain('Vending Machine Console');
    expect(root.textContent).toContain('Water');
    expect(root.textContent).toContain('€0.00');
    expect(root.textContent).toContain('Latest API exchange');
  });

  it('updates the dashboard and inspector after inserting a coin', async () => {
    const root = document.createElement('div');
    const app = new ReviewerApp(root, new FakeMachineClient());

    await app.mount();

    root
      .querySelector<HTMLButtonElement>('[data-coin="1"]')
      ?.click();

    await flush();

    expect(root.textContent).toContain('Inserted 1 coin');
    expect(root.textContent).toContain('€1.00');
    expect(root.textContent).toContain('/api/machine/insert-coin');
  });

  it('runs a small reviewer flow across load, purchase, return, and service interactions', async () => {
    const root = document.createElement('div');
    const client = new FakeMachineClient();
    const app = new ReviewerApp(root, client);

    await app.mount();

    root
      .querySelector<HTMLButtonElement>('[data-selector="water"]')
      ?.click();
    await flush();

    expect(root.textContent).toContain('Water dispensed');
    expect(root.textContent).toContain('Change: 10c × 1, 25c × 1.');

    root
      .querySelector<HTMLButtonElement>('[data-action="return-money"]')
      ?.click();
    await flush();

    expect(root.textContent).toContain('Money returned');

    root
      .querySelector<HTMLButtonElement>('[data-action="reset-service"]')
      ?.click();
    await flush();

    expect(client.servicePayloads).toHaveLength(1);
    expect(client.servicePayloads[0]?.productQuantities.water).toBe(10);
    expect(root.textContent).toContain('Machine serviced');
  });
});
