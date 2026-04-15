<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use VendingMachine\Application\Machine\Command\InsertCoinCommand;
use VendingMachine\Application\Machine\Command\ReturnInsertedMoneyCommand;
use VendingMachine\Application\Machine\Command\SelectProductCommand;
use VendingMachine\Application\Machine\Command\ServiceMachineCommand;
use VendingMachine\Application\Machine\Query\GetMachineStateQuery;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Request\CoinInputNormalizer;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Request\InsertCoinJsonRequest;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Request\JsonPayload;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Request\SelectProductJsonRequest;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Request\ServiceMachineJsonRequest;

/**
 * Maps the HTTP JSON contract into application commands and queries.
 *
 * Tip: this keeps Symfony requests and JSON field names outside Application.
 * See docs/architecture/http-api-boundary.md for the trade-offs.
 */
final readonly class MachineJsonRequestMapper
{
    public function __construct(
        private CoinInputNormalizer $coinInputNormalizer,
    ) {
    }

    public function createGetMachineStateQuery(): GetMachineStateQuery
    {
        return new GetMachineStateQuery();
    }

    public function createInsertCoinCommand(Request $request): InsertCoinCommand
    {
        $payload = JsonPayload::fromRequest($request);
        $insertCoinRequest = new InsertCoinJsonRequest(
            $this->coinInputNormalizer->coinCentsFromInsertPayload($payload),
        );

        return new InsertCoinCommand($insertCoinRequest->coinCents());
    }

    public function createReturnInsertedMoneyCommand(): ReturnInsertedMoneyCommand
    {
        return new ReturnInsertedMoneyCommand();
    }

    public function createServiceMachineCommand(Request $request): ServiceMachineCommand
    {
        $payload = JsonPayload::fromRequest($request);
        $serviceMachineRequest = new ServiceMachineJsonRequest(
            $payload->requiredObject('productQuantities'),
            $this->coinInputNormalizer->coinCountKeysToCents($payload->requiredObject('availableChangeCounts')),
        );

        return new ServiceMachineCommand(
            $serviceMachineRequest->productQuantities(),
            $serviceMachineRequest->availableChangeCounts(),
        );
    }

    public function createSelectProductCommand(Request $request): SelectProductCommand
    {
        $selectProductRequest = new SelectProductJsonRequest(
            JsonPayload::fromRequest($request)->requiredString('selector'),
        );

        return new SelectProductCommand($selectProductRequest->selector());
    }
}
