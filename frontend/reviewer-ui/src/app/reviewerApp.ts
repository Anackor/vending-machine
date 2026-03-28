import type {
  ApiExchange,
  MachineClient,
  MachineSnapshot,
  ServiceMachinePayload,
} from '../api/contracts';
import { MachineApiClient, MachineApiError } from '../api/machineApiClient';
import { DEFAULT_SERVICE_PAYLOAD, REVIEWER_COIN_OPTIONS } from './defaults';
import {
  formatCents,
  formatCoins,
  formatCounts,
  formatExchangeMeta,
  prettyJson,
} from './format';

type NoticeTone = 'info' | 'success' | 'error';

interface NoticeState {
  tone: NoticeTone;
  title: string;
  detail: string;
}

interface AppState {
  machine: MachineSnapshot | null;
  latestExchange: ApiExchange | null;
  notice: NoticeState | null;
  isBusy: boolean;
}

/**
 * Renders a small operator-style UI on top of the existing reviewer API.
 */
export class ReviewerApp {
  private readonly state: AppState = {
    machine: null,
    latestExchange: null,
    notice: null,
    isBusy: false,
  };

  public constructor(
    private readonly root: HTMLElement,
    private readonly client: MachineClient = new MachineApiClient(),
  ) {}

  public async mount(): Promise<void> {
    this.render();
    await this.refreshMachine();
  }

  private async refreshMachine(): Promise<void> {
    await this.runOperation(
      async () => {
        const result = await this.client.getMachineState();

        this.state.machine = result.data.machine;
        this.state.latestExchange = result.exchange;
        this.state.notice = {
          tone: 'info',
          title: 'Machine snapshot loaded',
          detail: 'The latest frontend-normalized machine state is now visible in the dashboard.',
        };
      },
      false,
    );
  }

  private async insertCoin(coins: number): Promise<void> {
    await this.runOperation(async () => {
      const result = await this.client.insertCoin(coins);

      this.state.machine = result.data.machine;
      this.state.latestExchange = result.exchange;
      this.state.notice = {
        tone: 'success',
        title: `Inserted ${formatCoins(coins)} coin`,
        detail: 'The new machine snapshot and the normalized API exchange are shown below.',
      };
    });
  }

  private async selectProduct(selector: string): Promise<void> {
    await this.runOperation(async () => {
      const result = await this.client.selectProduct(selector);

      this.state.machine = result.data.machine;
      this.state.latestExchange = result.exchange;
      this.state.notice = {
        tone: 'success',
        title: `${result.data.event.dispensedProduct.name} dispensed`,
        detail: `Change: ${formatCounts(result.data.event.dispensedChangeCounts)}.`,
      };
    });
  }

  private async returnInsertedMoney(): Promise<void> {
    await this.runOperation(async () => {
      const result = await this.client.returnInsertedMoney();

      this.state.machine = result.data.machine;
      this.state.latestExchange = result.exchange;
      this.state.notice = {
        tone: 'success',
        title: 'Money returned',
        detail: `Returned denominations: ${formatCounts(result.data.event.returnedCoinCounts)}.`,
      };
    });
  }

  private async serviceMachine(payload: ServiceMachinePayload): Promise<void> {
    await this.runOperation(async () => {
      const result = await this.client.serviceMachine(payload);

      this.state.machine = result.data.machine;
      this.state.latestExchange = result.exchange;
      this.state.notice = {
        tone: 'success',
        title: 'Machine serviced',
        detail: 'Stock and available change were updated successfully.',
      };
    });
  }

  private async runOperation(
    operation: () => Promise<void>,
    renderBusyState = true,
  ): Promise<void> {
    this.state.isBusy = true;

    if (renderBusyState) {
      this.render();
    }

    try {
      await operation();
    } catch (error) {
      if (error instanceof MachineApiError) {
        this.state.latestExchange = error.exchange;
        this.state.notice = {
          tone: 'error',
          title: error.code,
          detail: error.message,
        };
      } else {
        this.state.notice = {
          tone: 'error',
          title: 'unexpected_error',
          detail: 'The reviewer UI hit an unexpected client-side error.',
        };
      }
    } finally {
      this.state.isBusy = false;
      this.render();
    }
  }

  private render(): void {
    this.root.innerHTML = this.template();
    this.bindEvents();
  }

  private bindEvents(): void {
    this.root
      .querySelector('[data-action="reload"]')
      ?.addEventListener('click', () => void this.refreshMachine());

    this.root
      .querySelector('[data-action="return-money"]')
      ?.addEventListener('click', () => void this.returnInsertedMoney());

    this.root
      .querySelector('[data-action="reset-service"]')
      ?.addEventListener('click', () => void this.serviceMachine(DEFAULT_SERVICE_PAYLOAD));

    this.root
      .querySelectorAll<HTMLButtonElement>('[data-coin]')
      .forEach((button) =>
        button.addEventListener('click', () => {
          const coins = Number(button.dataset.coin);
          void this.insertCoin(coins);
        }),
      );

    this.root
      .querySelectorAll<HTMLButtonElement>('[data-selector]')
      .forEach((button) =>
        button.addEventListener('click', () => {
          const selector = button.dataset.selector;

          if (selector !== undefined) {
            void this.selectProduct(selector);
          }
        }),
      );

    this.root
      .querySelector('[data-service-form]')
      ?.addEventListener('submit', (event) => {
        event.preventDefault();
        void this.serviceMachine(this.servicePayloadFromForm());
      });
  }

  private template(): string {
    const machine = this.state.machine;
    const balance = machine === null ? 'EUR 0.00' : formatCents(machine.insertedBalanceCoins);
    const products = machine === null ? [] : machine.products;

    return `
      <div class="shell">
        <header class="hero card">
          <div>
            <p class="eyebrow">Reviewer UI</p>
            <h1>Vending Machine Console</h1>
            <p class="lede">
              A visual companion for the real HTTP API. Every interaction keeps the normalized request and response visible.
            </p>
          </div>
          <div class="hero-metrics">
            <article>
              <span>Machine</span>
              <strong>${machine?.machineId ?? 'loading'}</strong>
            </article>
            <article>
              <span>Inserted balance</span>
              <strong>${balance}</strong>
            </article>
            <article>
              <span>Products in stock</span>
              <strong>${products.reduce((total, product) => total + product.quantity, 0)}</strong>
            </article>
          </div>
        </header>

        ${this.noticeTemplate()}

        <main class="layout">
          <section class="primary-column">
            <section class="card">
              <div class="section-heading">
                <div>
                  <p class="eyebrow">Live state</p>
                  <h2>Machine snapshot</h2>
                </div>
                <button class="ghost-button" data-action="reload" ${this.disabledAttr()}>
                  Reload snapshot
                </button>
              </div>
              ${machine === null ? this.loadingTemplate() : this.machineSummaryTemplate(machine)}
            </section>

            <section class="card">
              <div class="section-heading">
                <div>
                  <p class="eyebrow">Products</p>
                  <h2>Buy from the machine</h2>
                </div>
              </div>
              <div class="product-grid">
                ${
                  products.length === 0
                    ? '<p class="empty-state">Products will appear here after the first machine snapshot loads.</p>'
                    : products.map((product) => this.productCardTemplate(product)).join('')
                }
              </div>
            </section>

            <section class="card">
              <div class="section-heading">
                <div>
                  <p class="eyebrow">Money flow</p>
                  <h2>Insert or return coins</h2>
                </div>
              </div>
              <div class="coin-actions">
                ${REVIEWER_COIN_OPTIONS.map(
                  (coin) => `
                    <button class="coin-button" data-coin="${coin}" ${this.disabledAttr()}>
                      Insert ${formatCoins(coin)}
                    </button>
                  `,
                ).join('')}
              </div>
              <button class="secondary-button" data-action="return-money" ${this.disabledAttr()}>
                Return inserted money
              </button>
            </section>
          </section>

          <aside class="secondary-column">
            <section class="card">
              <div class="section-heading">
                <div>
                  <p class="eyebrow">Operator panel</p>
                  <h2>Service machine</h2>
                </div>
                <button class="ghost-button" data-action="reset-service" ${this.disabledAttr()}>
                  Apply reviewer baseline
                </button>
              </div>
              ${this.serviceFormTemplate(machine)}
            </section>

            <section class="card inspector-card">
              <div class="section-heading">
                <div>
                  <p class="eyebrow">Inspector</p>
                  <h2>Latest API exchange</h2>
                </div>
              </div>
              ${this.exchangeTemplate()}
            </section>
          </aside>
        </main>
      </div>
    `;
  }

  private machineSummaryTemplate(machine: MachineSnapshot): string {
    return `
      <div class="summary-grid">
        <article class="summary-card">
          <span>Inserted balance</span>
          <strong>${formatCents(machine.insertedBalanceCoins)}</strong>
          <small>${machine.hasPendingBalance ? 'Pending balance present' : 'No pending balance'}</small>
        </article>
        <article class="summary-card">
          <span>Inserted coins</span>
          <strong>${formatCounts(machine.insertedCoins)}</strong>
          <small>Frontend-normalized coin denominations</small>
        </article>
        <article class="summary-card">
          <span>Available change</span>
          <strong>${formatCounts(machine.availableChangeCounts)}</strong>
          <small>Reviewer-visible machine reserve</small>
        </article>
      </div>
    `;
  }

  private productCardTemplate(machineProduct: MachineSnapshot['products'][number]): string {
    return `
      <article class="product-card">
        <div class="product-meta">
          <p class="selector">${machineProduct.selector.toUpperCase()}</p>
          <h3>${machineProduct.name}</h3>
        </div>
        <dl>
          <div>
            <dt>Price</dt>
            <dd>${formatCents(machineProduct.priceCoins)}</dd>
          </div>
          <div>
            <dt>Quantity</dt>
            <dd>${machineProduct.quantity}</dd>
          </div>
        </dl>
        <button
          class="primary-button"
          data-selector="${machineProduct.selector}"
          ${this.disabledAttr(!machineProduct.available)}
        >
          ${machineProduct.available ? 'Select product' : 'Out of stock'}
        </button>
      </article>
    `;
  }

  private serviceFormTemplate(machine: MachineSnapshot | null): string {
    const productDefaults = machine === null
      ? DEFAULT_SERVICE_PAYLOAD.productQuantities
      : Object.fromEntries(
          machine.products.map((product) => [product.selector, product.quantity]),
        );
    const changeDefaults = machine?.availableChangeCounts ?? DEFAULT_SERVICE_PAYLOAD.availableChangeCounts;

    return `
      <form data-service-form class="service-form">
        <div class="form-grid">
          <fieldset>
            <legend>Products</legend>
            ${this.numberInput('Water', 'product-water', productDefaults.water)}
            ${this.numberInput('Juice', 'product-juice', productDefaults.juice)}
            ${this.numberInput('Soda', 'product-soda', productDefaults.soda)}
          </fieldset>
          <fieldset>
            <legend>Available change</legend>
            ${this.numberInput('0.05 coin', 'change-005', changeDefaults['0.05'] ?? 0)}
            ${this.numberInput('0.10 coin', 'change-010', changeDefaults['0.10'] ?? 0)}
            ${this.numberInput('0.25 coin', 'change-025', changeDefaults['0.25'] ?? 0)}
            ${this.numberInput('1 coin', 'change-100', changeDefaults['1'] ?? 0)}
          </fieldset>
        </div>
        <button class="primary-button" type="submit" ${this.disabledAttr()}>
          Apply service update
        </button>
      </form>
    `;
  }

  private numberInput(label: string, name: string, value: number): string {
    return `
      <label class="number-field">
        <span>${label}</span>
        <input type="number" min="0" step="1" name="${name}" value="${value}" ${this.state.isBusy ? 'disabled' : ''} />
      </label>
    `;
  }

  private exchangeTemplate(): string {
    if (this.state.latestExchange === null) {
      return '<p class="empty-state">The first request will populate this panel automatically.</p>';
    }

    return `
      <div class="inspector-meta">
        <strong>${this.state.latestExchange.label}</strong>
        <span>${formatExchangeMeta(this.state.latestExchange)}</span>
      </div>
      <div class="code-pair">
        <div>
          <h3>Request</h3>
          <pre>${prettyJson(this.state.latestExchange.requestBody)}</pre>
        </div>
        <div>
          <h3>Response</h3>
          <pre>${prettyJson(this.state.latestExchange.responseBody)}</pre>
        </div>
      </div>
    `;
  }

  private noticeTemplate(): string {
    if (this.state.notice === null) {
      return '';
    }

    return `
      <section class="notice notice-${this.state.notice.tone}">
        <strong>${this.state.notice.title}</strong>
        <span>${this.state.notice.detail}</span>
      </section>
    `;
  }

  private loadingTemplate(): string {
    return '<p class="empty-state">Loading the latest machine snapshot...</p>';
  }

  private disabledAttr(disabled = this.state.isBusy): string {
    return disabled ? 'disabled' : '';
  }

  private servicePayloadFromForm(): ServiceMachinePayload {
    const form = this.root.querySelector<HTMLFormElement>('[data-service-form]');

    if (form === null) {
      return DEFAULT_SERVICE_PAYLOAD;
    }

    const formData = new FormData(form);

    return {
      productQuantities: {
        water: this.requiredInteger(formData, 'product-water'),
        juice: this.requiredInteger(formData, 'product-juice'),
        soda: this.requiredInteger(formData, 'product-soda'),
      },
      availableChangeCounts: {
        '0.05': this.requiredInteger(formData, 'change-005'),
        '0.10': this.requiredInteger(formData, 'change-010'),
        '0.25': this.requiredInteger(formData, 'change-025'),
        '1': this.requiredInteger(formData, 'change-100'),
      },
    };
  }

  private requiredInteger(formData: FormData, key: string): number {
    const rawValue = formData.get(key);
    const parsedValue = Number.parseInt(String(rawValue ?? 0), 10);

    return Number.isNaN(parsedValue) || parsedValue < 0 ? 0 : parsedValue;
  }
}
