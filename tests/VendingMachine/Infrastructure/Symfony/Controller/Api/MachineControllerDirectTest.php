<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Symfony\Controller\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\VendingMachine\Application\Machine\Double\InMemoryMachineRepository;
use Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Fixture\DefaultMachineFixture;
use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Application\Machine\Handler\GetMachineStateHandler;
use VendingMachine\Application\Machine\Handler\InsertCoinHandler;
use VendingMachine\Application\Machine\Handler\ReturnInsertedMoneyHandler;
use VendingMachine\Application\Machine\Handler\SelectProductHandler;
use VendingMachine\Application\Machine\Handler\ServiceMachineHandler;
use VendingMachine\Infrastructure\Symfony\Controller\Api\MachineController;
use VendingMachine\Infrastructure\Symfony\Controller\Api\MachineJsonRequestMapper;
use VendingMachine\Infrastructure\Symfony\Controller\Api\MachineJsonResponder;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter\CoinJsonPresenter;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter\MachineFailureJsonPresenter;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter\MachineSnapshotJsonPresenter;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Request\CoinInputNormalizer;

final class MachineControllerDirectTest extends TestCase
{
    public function testItReturnsStateDirectly(): void
    {
        [$controller, $getMachineStateHandler] = $this->controllerSuite();

        $response = $controller->machineState($getMachineStateHandler);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testItInsertsCoinDirectly(): void
    {
        [$controller, , $insertCoinHandler] = $this->controllerSuite();

        $response = $controller->insertCoin(
            $this->jsonRequest(['coins' => 0.25]),
            $insertCoinHandler,
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testItReturnsCoinDirectly(): void
    {
        [$controller, , , $returnInsertedMoneyHandler] = $this->controllerSuite(
            DefaultMachineFixture::machine(insertedCoinCounts: [10 => 1, 25 => 1]),
        );

        $response = $controller->returnCoin($returnInsertedMoneyHandler);
        $payload = $this->payload($response);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('money_returned', $this->eventType($payload));
    }

    public function testItSelectsProductDirectly(): void
    {
        [$controller, , , , $selectProductHandler] = $this->controllerSuite(
            DefaultMachineFixture::machine(
                availableChangeCounts: [10 => 1, 25 => 1],
                insertedCoinCounts: [100 => 1],
            ),
        );

        $response = $controller->selectProduct(
            $this->jsonRequest(['selector' => 'water']),
            $selectProductHandler,
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testItServicesTheMachineDirectly(): void
    {
        [$controller, , , , , $serviceMachineHandler] = $this->controllerSuite();

        $response = $controller->service(
            $this->jsonRequest([
                'productQuantities' => [
                    'water' => 6,
                    'juice' => 7,
                    'soda' => 4,
                ],
                'availableChangeCounts' => [
                    '0.05' => 4,
                    '0.10' => 5,
                    '0.25' => 6,
                    '1' => 2,
                ],
            ]),
            $serviceMachineHandler,
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testItMapsMissingMachinesToNotFoundResponsesDirectly(): void
    {
        [$controller, $getMachineStateHandler] = $this->controllerSuite(seedDefaultMachine: false);

        $response = $controller->machineState($getMachineStateHandler);
        $payload = $this->payload($response);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame('machine_not_found', $this->errorCode($payload));
    }

    public function testItMapsInvalidInsertCoinPayloadsToBadRequestResponsesDirectly(): void
    {
        [$controller, , $insertCoinHandler] = $this->controllerSuite();

        $response = $controller->insertCoin(
            $this->jsonRequest(['coins' => 0.249]),
            $insertCoinHandler,
        );
        $payload = $this->payload($response);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame('invalid_request', $this->errorCode($payload));
    }

    public function testItMapsMissingMachinesDuringInsertCoinToNotFoundResponsesDirectly(): void
    {
        [$controller, , $insertCoinHandler] = $this->controllerSuite(seedDefaultMachine: false);

        $response = $controller->insertCoin(
            $this->jsonRequest(['coins' => 0.25]),
            $insertCoinHandler,
        );
        $payload = $this->payload($response);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame('machine_not_found', $this->errorCode($payload));
    }

    public function testItMapsSelectProductFailuresToConflictResponsesDirectly(): void
    {
        [$controller, , , , $selectProductHandler] = $this->controllerSuite();

        $response = $controller->selectProduct(
            $this->jsonRequest(['selector' => 'water']),
            $selectProductHandler,
        );
        $payload = $this->payload($response);

        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
        self::assertSame('insufficient_balance', $this->errorCode($payload));
    }

    public function testItMapsInvalidSelectProductPayloadsToBadRequestResponsesDirectly(): void
    {
        [$controller, , , , $selectProductHandler] = $this->controllerSuite();

        $response = $controller->selectProduct(
            $this->jsonRequest(['selector' => 10]),
            $selectProductHandler,
        );
        $payload = $this->payload($response);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame('invalid_request', $this->errorCode($payload));
    }

    public function testItMapsInvalidServicePayloadsToBadRequestResponsesDirectly(): void
    {
        [$controller, , , , , $serviceMachineHandler] = $this->controllerSuite();

        $response = $controller->service(
            Request::create(
                '/api/machine/service',
                'POST',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: '[1]',
            ),
            $serviceMachineHandler,
        );
        $payload = $this->payload($response);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame('invalid_request', $this->errorCode($payload));
    }

    public function testItMapsServiceFailuresToConflictResponsesDirectly(): void
    {
        [$controller, , , , , $serviceMachineHandler] = $this->controllerSuite(
            DefaultMachineFixture::machine(insertedCoinCounts: [25 => 1]),
        );

        $response = $controller->service(
            $this->jsonRequest([
                'productQuantities' => [
                    'water' => 6,
                    'juice' => 7,
                    'soda' => 4,
                ],
                'availableChangeCounts' => [
                    '0.05' => 4,
                    '0.10' => 5,
                    '0.25' => 6,
                    '1' => 2,
                ],
            ]),
            $serviceMachineHandler,
        );
        $payload = $this->payload($response);

        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
        self::assertSame('pending_balance_during_service', $this->errorCode($payload));
    }

    public function testItMapsReturnCoinFailuresToNotFoundResponsesDirectly(): void
    {
        [$controller, , , $returnInsertedMoneyHandler] = $this->controllerSuite(seedDefaultMachine: false);

        $response = $controller->returnCoin($returnInsertedMoneyHandler);
        $payload = $this->payload($response);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame('machine_not_found', $this->errorCode($payload));
    }

    /**
     * @return array{
     *     0: MachineController,
     *     1: GetMachineStateHandler,
     *     2: InsertCoinHandler,
     *     3: ReturnInsertedMoneyHandler,
     *     4: SelectProductHandler,
     *     5: ServiceMachineHandler
     * }
     */
    private function controllerSuite(
        ?\VendingMachine\Domain\Machine\Machine $machine = null,
        bool $seedDefaultMachine = true,
    ): array {
        $machines = [];

        if ($seedDefaultMachine) {
            $machines['default'] = $machine ?? DefaultMachineFixture::machine();
        }

        $machineRepository = new InMemoryMachineRepository($machines);
        $machineSnapshotFactory = new MachineSnapshotFactory();
        $machineFailureFactory = new MachineFailureFactory();

        $coinPresenter = new CoinJsonPresenter();

        return [
            new MachineController(
                new MachineJsonRequestMapper(new CoinInputNormalizer()),
                new MachineJsonResponder(
                    $coinPresenter,
                    new MachineSnapshotJsonPresenter($coinPresenter),
                    new MachineFailureJsonPresenter($coinPresenter),
                ),
            ),
            new GetMachineStateHandler(
                $machineRepository,
                $machineSnapshotFactory,
                $machineFailureFactory,
            ),
            new InsertCoinHandler(
                $machineRepository,
                $machineSnapshotFactory,
                $machineFailureFactory,
            ),
            new ReturnInsertedMoneyHandler(
                $machineRepository,
                $machineSnapshotFactory,
                $machineFailureFactory,
            ),
            new SelectProductHandler(
                $machineRepository,
                $machineSnapshotFactory,
                $machineFailureFactory,
            ),
            new ServiceMachineHandler(
                $machineRepository,
                $machineSnapshotFactory,
                $machineFailureFactory,
            ),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jsonRequest(array $payload): Request
    {
        return Request::create(
            '/api/machine',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Response $response): array
    {
        $content = $response->getContent();

        self::assertIsString($content);

        $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($payload);

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function errorCode(array $payload): string
    {
        $error = $payload['error'] ?? null;

        self::assertIsArray($error);
        self::assertIsString($error['code'] ?? null);

        return $error['code'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function eventType(array $payload): string
    {
        $event = $payload['event'] ?? null;

        self::assertIsArray($event);
        self::assertIsString($event['type'] ?? null);

        return $event['type'];
    }
}
