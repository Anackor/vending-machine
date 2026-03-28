import type { ServiceMachinePayload } from '../api/contracts';

export const REVIEWER_COIN_OPTIONS = [0.05, 0.1, 0.25, 1] as const;

export const DEFAULT_SERVICE_PAYLOAD: ServiceMachinePayload = {
  productQuantities: {
    water: 10,
    juice: 8,
    soda: 5,
  },
  availableChangeCounts: {
    '0.05': 20,
    '0.10': 20,
    '0.25': 20,
    '1': 10,
  },
};
