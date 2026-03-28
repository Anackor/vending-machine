export type CoinAmount = number;
export type CoinCountMap = Record<string, number>;

export interface ProductSnapshot {
  selector: string;
  name: string;
  priceCoins: CoinAmount;
  quantity: number;
  available: boolean;
}

export interface MachineSnapshot {
  machineId: string;
  insertedBalanceCoins: CoinAmount;
  hasPendingBalance: boolean;
  insertedCoins: CoinCountMap;
  availableChangeCounts: CoinCountMap;
  products: ProductSnapshot[];
}

export interface GetMachineStateResponse {
  machine: MachineSnapshot;
}

export interface InsertCoinResponse {
  event: {
    type: 'coin_inserted';
    coins: CoinAmount;
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
    dispensedChangeCounts: CoinCountMap;
  };
  machine: MachineSnapshot;
}

export interface ReturnInsertedMoneyResponse {
  event: {
    type: 'money_returned';
    returnedCoinCounts: CoinCountMap;
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
  availableChangeCounts: CoinCountMap;
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
