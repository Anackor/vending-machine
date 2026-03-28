export interface ProductSnapshot {
  selector: string;
  name: string;
  priceCents: number;
  quantity: number;
  available: boolean;
}

export interface MachineSnapshot {
  machineId: string;
  insertedBalanceCents: number;
  hasPendingBalance: boolean;
  insertedCoins: Record<string, number>;
  availableChangeCounts: Record<string, number>;
  products: ProductSnapshot[];
}

export interface GetMachineStateResponse {
  machine: MachineSnapshot;
}

export interface InsertCoinResponse {
  event: {
    type: 'coin_inserted';
    coins: number;
  };
  machine: MachineSnapshot;
}

export interface SelectProductResponse {
  event: {
    type: 'product_selected';
    dispensedProduct: {
      name: string;
      selector: string;
    };
    dispensedChangeCounts: Record<string, number>;
  };
  machine: MachineSnapshot;
}

export interface ReturnInsertedMoneyResponse {
  event: {
    type: 'money_returned';
    returnedCoinCounts: Record<string, number>;
  };
  machine: MachineSnapshot;
}

export interface ServiceMachineResponse {
  event: {
    type: 'machine_serviced';
  };
  machine: MachineSnapshot;
}

export interface ServiceMachinePayload {
  productQuantities: Record<string, number>;
  availableChangeCounts: Record<string, number>;
}

export interface ApiExchange {
  label: string;
  method: string;
  path: string;
  requestBody: unknown | null;
  status: number;
  responseBody: unknown;
  occurredAt: string;
}

export interface ApiOperationResult<T> {
  data: T;
  exchange: ApiExchange;
}

export interface ApiErrorPayload {
  error: {
    code: string;
    message: string;
    context: Record<string, unknown>;
  };
}

export interface MachineClient {
  getMachineState(): Promise<ApiOperationResult<GetMachineStateResponse>>;
  insertCoin(coins: number): Promise<ApiOperationResult<InsertCoinResponse>>;
  selectProduct(
    selector: string,
  ): Promise<ApiOperationResult<SelectProductResponse>>;
  returnInsertedMoney(): Promise<
    ApiOperationResult<ReturnInsertedMoneyResponse>
  >;
  serviceMachine(
    payload: ServiceMachinePayload,
  ): Promise<ApiOperationResult<ServiceMachineResponse>>;
}
