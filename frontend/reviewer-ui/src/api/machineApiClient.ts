import type {
  ApiErrorPayload,
  ApiExchange,
  ApiOperationResult,
  GetMachineStateResponse,
  InsertCoinResponse,
  MachineClient,
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
      body: requestBody === undefined ? undefined : JSON.stringify(requestBody),
    });

    const responseBody = await this.responseBody(response);
    const exchange: ApiExchange = {
      label,
      method,
      path,
      requestBody: requestBody ?? null,
      status: response.status,
      responseBody,
      occurredAt: new Date().toISOString(),
    };

    if (!response.ok) {
      this.throwApiError(exchange, responseBody);
    }

    return {
      data: responseBody as T,
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
}
