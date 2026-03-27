<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Failure;

enum MachineFailureCode: string
{
    case ExactChangeUnavailable = 'exact_change_unavailable';
    case InsufficientBalance = 'insufficient_balance';
    case InvalidServiceConfiguration = 'invalid_service_configuration';
    case MachineNotFound = 'machine_not_found';
    case PendingBalanceDuringService = 'pending_balance_during_service';
    case ProductNotFound = 'product_not_found';
    case ProductOutOfStock = 'product_out_of_stock';
    case UnsupportedCoin = 'unsupported_coin';
}
