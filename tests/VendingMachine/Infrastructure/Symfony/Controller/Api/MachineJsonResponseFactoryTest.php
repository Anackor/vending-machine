<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Symfony\Controller\Api;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Application\Machine\Command\InsertCoinCommand;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Failure\MachineFailure;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Application\Machine\Result\GetMachineStateResult;
use VendingMachine\Application\Machine\Result\InsertCoinResult;
use VendingMachine\Application\Machine\Result\ReturnInsertedMoneyResult;
use VendingMachine\Application\Machine\Result\SelectProductResult;
use VendingMachine\Application\Machine\Result\ServiceMachineResult;
use VendingMachine\Infrastructure\Symfony\Controller\Api\MachineJsonResponseFactory;

final class MachineJsonResponseFactoryTest extends TestCase
{
    private MachineJsonResponseFactory $responseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = new MachineJsonResponseFactory();
    }

    public function testItBuildsTheMachineStateResponse(): void
    {
        $response = $this->responseFactory->machineState(
            new GetMachineStateResult(MachineSnapshotMother::create()),
        );
        $payload = $this->payload($response->getContent());
        $machine = $this->objectPayload($payload, 'machine');

        self::assertSame('default', $machine['machineId']);
        self::assertCount(3, $this->listPayload($machine, 'products'));
    }

    public function testItBuildsTheInsertCoinResponse(): void
    {
        $response = $this->responseFactory->insertCoin(
            new InsertCoinCommand(25),
            new InsertCoinResult(MachineSnapshotMother::create(insertedBalanceCents: 25, insertedCoins: [25 => 1])),
        );
        $payload = $this->payload($response->getContent());
        $event = $this->objectPayload($payload, 'event');

        self::assertSame('coin_inserted', $event['type']);
        self::assertSame(0.25, $event['coins']);
    }

    public function testItBuildsTheInsertCoinResponseWithWholeCoinValues(): void
    {
        $response = $this->responseFactory->insertCoin(
            new InsertCoinCommand(100),
            new InsertCoinResult(MachineSnapshotMother::create(insertedBalanceCents: 100, insertedCoins: [100 => 1])),
        );
        $payload = $this->payload($response->getContent());
        $event = $this->objectPayload($payload, 'event');

        self::assertSame('coin_inserted', $event['type']);
        self::assertSame(1, $event['coins']);
    }

    public function testItBuildsTheSelectProductResponse(): void
    {
        $response = $this->responseFactory->selectProduct(
            new SelectProductResult(
                'water',
                'Water',
                [10 => 1, 25 => 1],
                MachineSnapshotMother::create(),
            ),
        );
        $payload = $this->payload($response->getContent());
        $event = $this->objectPayload($payload, 'event');
        $dispensedProduct = $this->objectPayload($event, 'dispensedProduct');
        $dispensedChangeCounts = $this->objectPayload($event, 'dispensedChangeCounts');

        self::assertSame('product_selected', $event['type']);
        self::assertSame('water', $dispensedProduct['selector']);
        self::assertSame(1, $this->intValue($dispensedChangeCounts, '10'));
    }

    public function testItBuildsTheReturnInsertedMoneyResponse(): void
    {
        $response = $this->responseFactory->returnInsertedMoney(
            new ReturnInsertedMoneyResult(
                [10 => 1, 25 => 1],
                MachineSnapshotMother::create(),
            ),
        );
        $payload = $this->payload($response->getContent());
        $event = $this->objectPayload($payload, 'event');
        $returnedCoinCounts = $this->objectPayload($event, 'returnedCoinCounts');

        self::assertSame('money_returned', $event['type']);
        self::assertSame(1, $this->intValue($returnedCoinCounts, '25'));
    }

    public function testItBuildsTheServiceMachineResponse(): void
    {
        $response = $this->responseFactory->serviceMachine(
            new ServiceMachineResult(MachineSnapshotMother::create()),
        );
        $payload = $this->payload($response->getContent());
        $event = $this->objectPayload($payload, 'event');

        self::assertSame('machine_serviced', $event['type']);
    }

    public function testItBuildsApplicationFailureResponsesWithStatusMapping(): void
    {
        $response = $this->responseFactory->machineOperationFailed(
            new MachineOperationFailed(
                new MachineFailure(
                    MachineFailureCode::InsufficientBalance,
                    'Insufficient balance.',
                    ['machineId' => 'default'],
                ),
            ),
        );
        $payload = $this->payload($response->getContent());
        $error = $this->objectPayload($payload, 'error');

        self::assertSame(409, $response->getStatusCode());
        self::assertSame('insufficient_balance', $error['code']);
    }

    public function testItBuildsInvalidRequestResponses(): void
    {
        $response = $this->responseFactory->invalidRequest(
            new InvalidArgumentException('Field "coins" must be numeric.'),
        );
        $payload = $this->payload($response->getContent());
        $error = $this->objectPayload($payload, 'error');

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('invalid_request', $error['code']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string|false $content): array
    {
        self::assertIsString($content);

        $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($payload);

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<array<string, mixed>>
     */
    private function listPayload(array $payload, string $field): array
    {
        $value = $payload[$field] ?? null;

        self::assertIsArray($value);

        foreach ($value as $item) {
            self::assertIsArray($item);
        }

        /** @var list<array<string, mixed>> $value */
        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function objectPayload(array $payload, string $field): array
    {
        $value = $payload[$field] ?? null;

        self::assertIsArray($value);

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function intValue(array $payload, string $field): int
    {
        $value = $payload[$field] ?? null;

        self::assertIsInt($value);

        return $value;
    }
}
