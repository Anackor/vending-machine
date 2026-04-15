<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api;

use JsonException;
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
 * Maps validated HTTP JSON contracts into application commands and queries.
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

    /**
     * @throws JsonException
     */
    public function createInsertCoinCommand(Request $request): InsertCoinCommand
    {
        $insertCoinRequest = InsertCoinJsonRequest::fromPayload(
            JsonPayload::fromRequest($request),
            $this->coinInputNormalizer,
        );

        return new InsertCoinCommand($insertCoinRequest->coinCents());
    }

    public function createReturnInsertedMoneyCommand(): ReturnInsertedMoneyCommand
    {
        return new ReturnInsertedMoneyCommand();
    }

    /**
     * @throws JsonException
     */
    public function createServiceMachineCommand(Request $request): ServiceMachineCommand
    {
        $serviceMachineRequest = ServiceMachineJsonRequest::fromPayload(
            JsonPayload::fromRequest($request),
            $this->coinInputNormalizer,
        );

        return new ServiceMachineCommand(
            $serviceMachineRequest->productQuantities(),
            $serviceMachineRequest->availableChangeCounts(),
        );
    }

    /**
     * @throws JsonException
     */
    public function createSelectProductCommand(Request $request): SelectProductCommand
    {
        $selectProductRequest = SelectProductJsonRequest::fromPayload(JsonPayload::fromRequest($request));

        return new SelectProductCommand($selectProductRequest->selector());
    }
}
