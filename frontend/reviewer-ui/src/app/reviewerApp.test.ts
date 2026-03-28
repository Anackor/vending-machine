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
  insertedBalanceCoins: 0,
  hasPendingBalance: false,
  insertedCoins: {},
  availableChangeCounts: { '0.05': 20, '0.10': 20, '0.25': 20, '1': 10 },
  products: [
    { selector: 'water', name: 'Water', priceCoins: 0.65, quantity: 10, available: true },
    { selector: 'juice', name: 'Juice', priceCoins: 1, quantity: 8, available: true },
    { selector: 'soda', name: 'Soda', priceCoins: 1.5, quantity: 5, available: true },
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
        machine: { ...initialMachine, insertedBalanceCoins: coins === 1 ? 1 : 0.25 },
      },
      exchange: this.exchange('POST', '/api/machine/insert-coin', { coins }, {
        event: { type: 'coin_inserted', coins },
      }),
    };
  }

  public async selectProduct(
    selector: string,
  ): Promise<ApiOperationResult<SelectProductResponse>> {
    const machine = {
      ...initialMachine,
      insertedBalanceCoins: 0,
      products: initialMachine.products.map((product) =>
        product.selector === selector
          ? { ...product, quantity: product.quantity - 1 }
          : product,
      ),
    };
    const event = {
      type: 'product_selected' as const,
      dispensedProduct: { name: 'Water', selector },
      dispensedChangeCounts: { '0.10': 1, '0.25': 1 },
    };

    return {
      data: {
        event,
        machine,
      },
      exchange: this.exchange('POST', '/api/machine/select-product', { selector }, { event, machine }),
    };
  }

  public async returnInsertedMoney(): Promise<
    ApiOperationResult<ReturnInsertedMoneyResponse>
  > {
    const event = {
      type: 'money_returned' as const,
      returnedCoinCounts: { '1': 1 },
    };

    return {
      data: {
        event,
        machine: initialMachine,
      },
      exchange: this.exchange('POST', '/api/machine/return-coin', null, { event, machine: initialMachine }),
    };
  }

  public async serviceMachine(
    payload: ServiceMachinePayload,
  ): Promise<ApiOperationResult<ServiceMachineResponse>> {
    this.servicePayloads.push(payload);

    const machine = {
      ...initialMachine,
      availableChangeCounts: payload.availableChangeCounts,
      products: initialMachine.products.map((product) => ({
        ...product,
        quantity: payload.productQuantities[product.selector],
      })),
    };

    return {
      data: {
        event: { type: 'machine_serviced' },
        machine,
      },
      exchange: this.exchange('POST', '/api/machine/service', payload, {
        event: { type: 'machine_serviced' },
        machine,
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
    expect(root.textContent).toContain('EUR 0.00');
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
    expect(root.textContent).toContain('EUR 1.00');
    expect(root.textContent).toContain('/api/machine/insert-coin');
    expect(root.textContent).toContain('"coins": 1');
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
    expect(root.textContent).toContain('Change: 0.10 x 1, 0.25 x 1.');
    expect(root.textContent).toContain('"dispensedChangeCounts": {');
    expect(root.textContent).toContain('"0.25": 1');

    root
      .querySelector<HTMLButtonElement>('[data-action="return-money"]')
      ?.click();
    await flush();

    expect(root.textContent).toContain('Money returned');
    expect(root.textContent).toContain('"returnedCoinCounts": {');
    expect(root.textContent).toContain('"1": 1');

    root
      .querySelector<HTMLButtonElement>('[data-action="reset-service"]')
      ?.click();
    await flush();

    expect(client.servicePayloads).toHaveLength(1);
    expect(client.servicePayloads[0]?.availableChangeCounts['0.25']).toBe(20);
    expect(root.textContent).toContain('Machine serviced');
    expect(root.textContent).toContain('"availableChangeCounts": {');
    expect(root.textContent).toContain('"0.05": 20');
  });
});
