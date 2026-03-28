import type {
  ApiErrorPayload,
  ApiExchange,
  ApiOperationResult,
  CoinAmount,
  CoinCountMap,
  GetMachineStateResponse,
  InsertCoinResponse,
  MachineClient,
  MachineSnapshot,
  ProductSnapshot,
  ReturnInsertedMoneyResponse,
  SelectProductResponse,
  ServiceMachinePayload,
  ServiceMachineResponse,
} from './contracts';

/**
 * Calls the reviewer-facing vending machine API and records the latest HTTP exchange details.
 */
export class MachineApiError extends Error {
  public constructor(
    public readonly code: string,
    message: string,
    public readonly exchange: ApiExchange,
    public readonly context: Record<string, unknown> = {},
  ) {
    super(message);
    this.name = 'MachineApiError';
  }
}

/**
 * Thin browser client that keeps transport details out of the visual app layer.
 */
export class MachineApiClient implements MachineClient {
  public constructor(private readonly basePath = '/api/machine') {}

  public getMachineState(): Promise<ApiOperationResult<GetMachineStateResponse>> {
    return this.request<GetMachineStateResponse>('Load machine state', 'GET', '');
  }

  public insertCoin(coins: number): Promise<ApiOperationResult<InsertCoinResponse>> {
    return this.request<InsertCoinResponse>('Insert coin', 'POST', '/insert-coin', {
      coins,
    });
  }

  public selectProduct(
    selector: string,
  ): Promise<ApiOperationResult<SelectProductResponse>> {
    return this.request<SelectProductResponse>(
      'Select product',
      'POST',
      '/select-product',
      {
        selector,
      },
    );
  }

  public returnInsertedMoney(): Promise<
    ApiOperationResult<ReturnInsertedMoneyResponse>
  > {
    return this.request<ReturnInsertedMoneyResponse>(
      'Return inserted money',
      'POST',
      '/return-coin',
    );
  }

  public serviceMachine(
    payload: ServiceMachinePayload,
  ): Promise<ApiOperationResult<ServiceMachineResponse>> {
    return this.request<ServiceMachineResponse>(
      'Service machine',
      'POST',
      '/service',
      payload,
    );
  }

  private async request<T>(
    label: string,
    method: string,
    suffix: string,
    requestBody?: unknown,
  ): Promise<ApiOperationResult<T>> {
    const path = `${this.basePath}${suffix}`;
    const response = await fetch(path, {
      method,
      headers: requestBody === undefined ? undefined : { 'Content-Type': 'application/json' },
      body: requestBody === undefined ? undefined : JSON.stringify(this.toWireRequestBody(requestBody)),
    });

    const rawResponseBody = await this.responseBody(response);
    const normalizedResponseBody = this.normalizeResponseBody(rawResponseBody);
    const exchange: ApiExchange = {
      label,
      method,
      path,
      requestBody: requestBody ?? null,
      status: response.status,
      responseBody: normalizedResponseBody,
      occurredAt: new Date().toISOString(),
    };

    if (!response.ok) {
      this.throwApiError(exchange, normalizedResponseBody);
    }

    return {
      data: normalizedResponseBody as T,
      exchange,
    };
  }

  private async responseBody(response: Response): Promise<unknown> {
    const bodyText = await response.text();

    if (bodyText.trim() === '') {
      return null;
    }

    try {
      return JSON.parse(bodyText) as unknown;
    } catch {
      return bodyText;
    }
  }

  private throwApiError(exchange: ApiExchange, responseBody: unknown): never {
    if (this.isApiErrorPayload(responseBody)) {
      throw new MachineApiError(
        responseBody.error.code,
        responseBody.error.message,
        exchange,
        responseBody.error.context,
      );
    }

    throw new MachineApiError(
      'http_error',
      `The API returned status ${exchange.status}.`,
      exchange,
    );
  }

  private isApiErrorPayload(responseBody: unknown): responseBody is ApiErrorPayload {
    if (typeof responseBody !== 'object' || responseBody === null) {
      return false;
    }

    return 'error' in responseBody;
  }

  private toWireRequestBody(requestBody: unknown): unknown {
    return requestBody;
  }

  private normalizeResponseBody(responseBody: unknown): unknown {
    if (this.isGetMachineStateResponse(responseBody)) {
      const responseRecord = this.recordOfUnknown(responseBody);

      return { machine: this.normalizeMachineSnapshot(this.recordOfUnknown(responseRecord.machine)) };
    }

    if (this.isInsertCoinResponse(responseBody)) {
      const responseRecord = this.recordOfUnknown(responseBody);

      return {
        event: this.recordOfUnknown(responseRecord.event),
        machine: this.normalizeMachineSnapshot(this.recordOfUnknown(responseRecord.machine)),
      };
    }

    if (this.isSelectProductResponse(responseBody)) {
      const responseRecord = this.recordOfUnknown(responseBody);
      const event = this.recordOfUnknown(responseRecord.event);

      return {
        event: {
          ...event,
          dispensedChangeCounts: this.normalizeCoinCounts(this.recordOfNumbers(event.dispensedChangeCounts)),
        },
        machine: this.normalizeMachineSnapshot(this.recordOfUnknown(responseRecord.machine)),
      };
    }

    if (this.isReturnInsertedMoneyResponse(responseBody)) {
      const responseRecord = this.recordOfUnknown(responseBody);
      const event = this.recordOfUnknown(responseRecord.event);

      return {
        event: {
          ...event,
          returnedCoinCounts: this.normalizeCoinCounts(this.recordOfNumbers(event.returnedCoinCounts)),
        },
        machine: this.normalizeMachineSnapshot(this.recordOfUnknown(responseRecord.machine)),
      };
    }

    if (this.isServiceMachineResponse(responseBody)) {
      const responseRecord = this.recordOfUnknown(responseBody);

      return {
        event: this.recordOfUnknown(responseRecord.event),
        machine: this.normalizeMachineSnapshot(this.recordOfUnknown(responseRecord.machine)),
      };
    }

    if (this.isApiErrorPayload(responseBody)) {
      return {
        error: {
          code: responseBody.error.code,
          message: this.normalizeErrorMessage(responseBody.error.code, responseBody.error.message),
          context: this.normalizeObject(responseBody.error.context),
        },
      };
    }

    return responseBody;
  }

  private normalizeMachineSnapshot(rawMachine: Record<string, unknown>): MachineSnapshot {
    return {
      machineId: String(rawMachine.machineId),
      insertedBalanceCoins: this.normalizedCoinAmount(
        rawMachine.insertedBalanceCoins,
        rawMachine.insertedBalanceCents,
      ),
      hasPendingBalance: Boolean(rawMachine.hasPendingBalance),
      insertedCoins: this.normalizeCoinCounts(this.recordOfNumbers(rawMachine.insertedCoins)),
      availableChangeCounts: this.normalizeCoinCounts(
        this.recordOfNumbers(rawMachine.availableChangeCounts),
      ),
      products: Array.isArray(rawMachine.products)
        ? rawMachine.products.map((product) => this.normalizeProductSnapshot(product))
        : [],
    };
  }

  private normalizeProductSnapshot(rawProduct: unknown): ProductSnapshot {
    const product = this.recordOfUnknown(rawProduct);

    return {
      selector: String(product.selector),
      name: String(product.name),
      priceCoins: this.normalizedCoinAmount(product.priceCoins, product.priceCents),
      quantity: Number(product.quantity),
      available: Boolean(product.available),
    };
  }

  private normalizeObject(value: Record<string, unknown>): Record<string, unknown> {
    const normalized: Record<string, unknown> = {};

    for (const [key, currentValue] of Object.entries(value)) {
      if (this.isCoinCountContainerKey(key) && this.isRecord(currentValue)) {
        normalized[key] = this.normalizeCoinCounts(this.recordOfNumbers(currentValue));
        continue;
      }

      if (key === 'coinCents' && typeof currentValue === 'number') {
        normalized.coins = this.centsToCoins(currentValue);
        continue;
      }

      if (key.endsWith('Cents') && typeof currentValue === 'number') {
        normalized[`${key.slice(0, -5)}Coins`] = this.centsToCoins(currentValue);
        continue;
      }

      if (this.isRecord(currentValue)) {
        normalized[key] = this.normalizeObject(currentValue);
        continue;
      }

      normalized[key] = currentValue;
    }

    return normalized;
  }

  private normalizeCoinCounts(rawCounts: Record<string, number>): CoinCountMap {
    if (this.coinCountsAlreadyUseCoins(rawCounts)) {
      return Object.fromEntries(
        Object.entries(rawCounts)
          .map(([denomination, quantity]) => [
            this.formatCoinLiteral(Number(denomination)),
            quantity,
          ] as const)
          .sort(([left], [right]) => Number(left) - Number(right)),
      );
    }

    const normalized = Object.entries(rawCounts)
      .map(([denomination, quantity]) => [
        this.coinKeyFromCents(Number(denomination)),
        quantity,
      ] as const)
      .sort(([left], [right]) => Number(left) - Number(right));

    return Object.fromEntries(normalized);
  }

  private normalizeErrorMessage(code: string, message: string): string {
    switch (code) {
      case 'exact_change_unavailable':
      case 'insufficient_balance':
      case 'unsupported_coin':
        return message.replace(/"(\d+)"/g, (_match, cents) => {
          return `"${this.formatCoinLiteral(this.centsToCoins(Number(cents)))}"`;
        });
      default:
        return message;
    }
  }

  private centsToCoins(cents: number): CoinAmount {
    return Number((cents / 100).toFixed(2));
  }

  private formatCoinLiteral(coins: number): string {
    return Number.isInteger(coins) ? coins.toFixed(0) : coins.toFixed(2);
  }

  private coinKeyFromCents(cents: number): string {
    return this.formatCoinLiteral(this.centsToCoins(cents));
  }

  private normalizedCoinAmount(coins: unknown, cents: unknown): CoinAmount {
    if (typeof coins === 'number') {
      return Number(coins.toFixed(2));
    }

    return this.centsToCoins(Number(cents));
  }

  private isGetMachineStateResponse(value: unknown): value is Record<string, unknown> {
    return this.isRecord(value) && 'machine' in value && !('event' in value);
  }

  private isInsertCoinResponse(value: unknown): value is Record<string, unknown> {
    return this.hasEventType(value, 'coin_inserted');
  }

  private isSelectProductResponse(value: unknown): value is Record<string, unknown> {
    return this.hasEventType(value, 'product_selected');
  }

  private isReturnInsertedMoneyResponse(value: unknown): value is Record<string, unknown> {
    return this.hasEventType(value, 'money_returned');
  }

  private isServiceMachineResponse(value: unknown): value is Record<string, unknown> {
    return this.hasEventType(value, 'machine_serviced');
  }

  private hasEventType(value: unknown, expectedType: string): value is Record<string, unknown> {
    if (!this.isRecord(value) || !('event' in value) || !this.isRecord(value.event)) {
      return false;
    }

    return value.event.type === expectedType;
  }

  private isCoinCountContainerKey(key: string): boolean {
    return [
      'insertedCoins',
      'availableChangeCounts',
      'returnedCoinCounts',
      'dispensedChangeCounts',
    ].includes(key);
  }

  private coinCountsAlreadyUseCoins(rawCounts: Record<string, number>): boolean {
    return Object.keys(rawCounts).some((denomination) => {
      return denomination.includes('.') || Number(denomination) <= 1;
    });
  }

  private recordOfUnknown(value: unknown): Record<string, unknown> {
    return this.isRecord(value) ? value : {};
  }

  private recordOfNumbers(value: unknown): Record<string, number> {
    if (!this.isRecord(value)) {
      return {};
    }

    return Object.fromEntries(
      Object.entries(value).filter(([, currentValue]) => typeof currentValue === 'number'),
    ) as Record<string, number>;
  }

  private isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
  }
}
