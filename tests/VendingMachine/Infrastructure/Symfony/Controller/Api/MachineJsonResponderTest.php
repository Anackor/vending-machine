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
use VendingMachine\Infrastructure\Symfony\Controller\Api\MachineJsonResponder;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter\CoinJsonPresenter;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter\MachineFailureJsonPresenter;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter\MachineSnapshotJsonPresenter;

final class MachineJsonResponderTest extends TestCase
{
    private MachineJsonResponder $responder;

    protected function setUp(): void
    {
        parent::setUp();

        $coinPresenter = new CoinJsonPresenter();
        $this->responder = new MachineJsonResponder(
            $coinPresenter,
            new MachineSnapshotJsonPresenter($coinPresenter),
            new MachineFailureJsonPresenter($coinPresenter),
        );
    }

    public function testItBuildsTheMachineStateResponse(): void
    {
        $response = $this->responder->machineState(
            new GetMachineStateResult(MachineSnapshotMother::create(insertedBalanceCents: 25, insertedCoins: [25 => 1])),
        );
        $payload = $this->payload($response->getContent());
        $machine = $this->objectPayload($payload, 'machine');
        $insertedCoins = $this->objectPayload($machine, 'insertedCoins');
        $availableChangeCounts = $this->objectPayload($machine, 'availableChangeCounts');
        $products = $this->listPayload($machine, 'products');

        self::assertSame('default', $machine['machineId']);
        self::assertSame(0.25, $this->numberValue($machine, 'insertedBalanceCoins'));
        self::assertArrayNotHasKey('insertedBalanceCents', $machine);
        self::assertSame(1, $this->intValue($insertedCoins, '0.25'));
        self::assertSame(20, $this->intValue($availableChangeCounts, '0.10'));
        self::assertCount(3, $products);
        self::assertSame(1.5, $this->numberValue($this->productPayload($products, 'soda'), 'priceCoins'));
        self::assertArrayNotHasKey('priceCents', $this->productPayload($products, 'soda'));
    }

    public function testItBuildsTheInsertCoinResponse(): void
    {
        $response = $this->responder->insertCoin(
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
        $response = $this->responder->insertCoin(
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
        $response = $this->responder->selectProduct(
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
        self::assertSame(1, $this->intValue($dispensedChangeCounts, '0.10'));
        self::assertSame(1, $this->intValue($dispensedChangeCounts, '0.25'));
    }

    public function testItBuildsTheReturnInsertedMoneyResponse(): void
    {
        $response = $this->responder->returnInsertedMoney(
            new ReturnInsertedMoneyResult(
                [10 => 1, 25 => 1],
                MachineSnapshotMother::create(),
            ),
        );
        $payload = $this->payload($response->getContent());
        $event = $this->objectPayload($payload, 'event');
        $returnedCoinCounts = $this->objectPayload($event, 'returnedCoinCounts');

        self::assertSame('money_returned', $event['type']);
        self::assertSame(1, $this->intValue($returnedCoinCounts, '0.10'));
        self::assertSame(1, $this->intValue($returnedCoinCounts, '0.25'));
    }

    public function testItBuildsTheServiceMachineResponse(): void
    {
        $response = $this->responder->serviceMachine(
            new ServiceMachineResult(MachineSnapshotMother::create()),
        );
        $payload = $this->payload($response->getContent());
        $event = $this->objectPayload($payload, 'event');

        self::assertSame('machine_serviced', $event['type']);
    }

    public function testItBuildsApplicationFailureResponsesWithCoinsAndStatusMapping(): void
    {
        $response = $this->responder->machineOperationFailed(
            new MachineOperationFailed(
                new MachineFailure(
                    MachineFailureCode::ExactChangeUnavailable,
                    'Exact change "150" cannot be returned for selector "soda".',
                    [
                        'machineId' => 'default',
                        'coinCents' => 150,
                        'requiredBalanceCents' => 150,
                    ],
                ),
            ),
        );
        $payload = $this->payload($response->getContent());
        $error = $this->objectPayload($payload, 'error');
        $context = $this->objectPayload($error, 'context');

        self::assertSame(409, $response->getStatusCode());
        self::assertSame('exact_change_unavailable', $error['code']);
        self::assertSame('Exact change "1.50" cannot be returned for selector "soda".', $error['message']);
        self::assertSame(1.5, $this->numberValue($context, 'coins'));
        self::assertSame(1.5, $this->numberValue($context, 'requiredBalanceCoins'));
        self::assertArrayNotHasKey('coinCents', $context);
    }

    public function testItBuildsInvalidRequestResponses(): void
    {
        $response = $this->responder->invalidRequest(
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

    /**
     * @param array<string, mixed> $payload
     */
    private function numberValue(array $payload, string $field): int|float
    {
        $value = $payload[$field] ?? null;

        self::assertTrue(is_int($value) || is_float($value));

        return $value;
    }

    /**
     * @param list<array<string, mixed>> $products
     *
     * @return array<string, mixed>
     */
    private function productPayload(array $products, string $selector): array
    {
        foreach ($products as $product) {
            if (($product['selector'] ?? null) === $selector) {
                return $product;
            }
        }

        self::fail(sprintf('Product "%s" was not found in the payload.', $selector));
    }
}
