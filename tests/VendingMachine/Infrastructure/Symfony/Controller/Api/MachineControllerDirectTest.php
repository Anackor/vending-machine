<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Symfony\Controller\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
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
use VendingMachine\Infrastructure\Symfony\Controller\Api\MachineJsonRequestFactory;
use VendingMachine\Infrastructure\Symfony\Controller\Api\MachineJsonResponseFactory;

final class MachineControllerDirectTest extends TestCase
{
    public function testItReturnsStateDirectly(): void
    {
        [$controller, $getMachineStateHandler] = $this->controllerSuite();

        $response = $controller->machineState($getMachineStateHandler);

        self::assertSame(200, $response->getStatusCode());
    }

    public function testItInsertsCoinDirectly(): void
    {
        [$controller, , $insertCoinHandler] = $this->controllerSuite();

        $response = $controller->insertCoin(
            $this->jsonRequest(['coinCents' => 25]),
            $insertCoinHandler,
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testItReturnsCoinDirectly(): void
    {
        [$controller, , , $returnInsertedMoneyHandler] = $this->controllerSuite(
            DefaultMachineFixture::machine(insertedCoinCounts: [25 => 1]),
        );

        $response = $controller->returnCoin($returnInsertedMoneyHandler);

        self::assertSame(200, $response->getStatusCode());
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

        self::assertSame(200, $response->getStatusCode());
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
                    '5' => 4,
                    '10' => 5,
                    '25' => 6,
                    '100' => 2,
                ],
            ]),
            $serviceMachineHandler,
        );

        self::assertSame(200, $response->getStatusCode());
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
    private function controllerSuite(?\VendingMachine\Domain\Machine\Machine $machine = null): array
    {
        $machineRepository = new InMemoryMachineRepository([
            'default' => $machine ?? DefaultMachineFixture::machine(),
        ]);
        $machineSnapshotFactory = new MachineSnapshotFactory();
        $machineFailureFactory = new MachineFailureFactory();

        return [
            new MachineController(
                new MachineJsonRequestFactory(),
                new MachineJsonResponseFactory(),
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
}
