<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Symfony\Controller\Api;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Fixture\DefaultMachineFixture;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\ValueObject\MachineId;
use VendingMachine\Domain\Machine\ValueObject\Selector;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Mapper\MachineDocumentMapper;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\MongoDBMachineRepository;

final class MachineControllerTest extends KernelTestCase
{
    private Database $database;
    private HttpKernelInterface $httpKernel;
    private MachineRepository $machineRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->httpKernel = $kernel;
        $client = new Client($this->environmentVariable('MONGODB_URI'));

        $this->database = $client->selectDatabase($this->environmentVariable('MONGODB_DATABASE'));
        $this->machineRepository = new MongoDBMachineRepository(
            $this->database,
            new MachineDocumentMapper(),
        );

        $this->clearMachineCollection();
    }

    protected function tearDown(): void
    {
        $this->clearMachineCollection();
        self::ensureKernelShutdown();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsTheCurrentMachineStateThroughHttp(): void
    {
        $this->seedDefaultMachine();

        $response = $this->request('GET', '/api/machine');
        $payload = $this->payload($response);
        $machine = $this->machinePayload($payload);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('default', $machine['machineId']);
        self::assertSame(0, $this->numberValue($machine, 'insertedBalanceCoins'));
        self::assertArrayNotHasKey('insertedBalanceCents', $machine);
        self::assertFalse($machine['hasPendingBalance']);
        self::assertCount(3, $this->productsPayload($machine));
        self::assertSame(10, $this->productQuantity($payload, 'water'));
        self::assertSame(8, $this->productQuantity($payload, 'juice'));
        self::assertSame(5, $this->productQuantity($payload, 'soda'));
    }

    #[Test]
    public function itInsertsCoinThroughTheHttpInterfaceAndPersistsTheUpdatedState(): void
    {
        $this->seedDefaultMachine();

        $response = $this->request('POST', '/api/machine/insert-coin', ['coins' => 0.25]);
        $payload = $this->payload($response);
        $event = $this->eventPayload($payload);
        $machine = $this->machinePayload($payload);
        $insertedCoins = $this->objectPayload($machine, 'insertedCoins');
        $reloadedMachine = $this->machineRepository->find(MachineId::default());

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('coin_inserted', $event['type']);
        self::assertSame(0.25, $event['coins']);
        self::assertSame(0.25, $this->numberValue($machine, 'insertedBalanceCoins'));
        self::assertSame(1, $this->countValue($insertedCoins, '0.25'));
        self::assertNotNull($reloadedMachine);
        self::assertSame(25, $reloadedMachine->insertedBalance()->cents());
    }

    #[Test]
    public function itStillAcceptsCoinCentsThroughTheHttpInterfaceForCompatibility(): void
    {
        $this->seedDefaultMachine();

        $response = $this->request('POST', '/api/machine/insert-coin', ['coinCents' => 25]);
        $payload = $this->payload($response);
        $event = $this->eventPayload($payload);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('coin_inserted', $event['type']);
        self::assertSame(0.25, $event['coins']);
    }

    #[Test]
    public function itSelectsProductAndReturnsChangeThroughTheHttpInterface(): void
    {
        $this->seedDefaultMachine(DefaultMachineFixture::machine(
            availableChangeCounts: [10 => 1, 25 => 1],
            insertedCoinCounts: [100 => 1],
        ));

        $response = $this->request('POST', '/api/machine/select-product', ['selector' => 'water']);
        $payload = $this->payload($response);
        $event = $this->eventPayload($payload);
        $dispensedProduct = $this->objectPayload($event, 'dispensedProduct');
        $dispensedChangeCounts = $this->objectPayload($event, 'dispensedChangeCounts');
        $machine = $this->machinePayload($payload);
        $reloadedMachine = $this->machineRepository->find(MachineId::default());

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('product_selected', $event['type']);
        self::assertSame('water', $dispensedProduct['selector']);
        self::assertSame('Water', $dispensedProduct['name']);
        self::assertSame(1, $this->countValue($dispensedChangeCounts, '0.10'));
        self::assertSame(1, $this->countValue($dispensedChangeCounts, '0.25'));
        self::assertSame(0, $this->numberValue($machine, 'insertedBalanceCoins'));
        self::assertNotNull($reloadedMachine);
        self::assertSame(9, $reloadedMachine->productStockFor(Selector::fromString('water'))?->quantity());
    }

    #[Test]
    public function itReturnsInsertedMoneyThroughTheHttpInterface(): void
    {
        $this->seedDefaultMachine(DefaultMachineFixture::machine(
            insertedCoinCounts: [10 => 1, 25 => 1],
        ));

        $response = $this->request('POST', '/api/machine/return-coin');
        $payload = $this->payload($response);
        $event = $this->eventPayload($payload);
        $returnedCoinCounts = $this->objectPayload($event, 'returnedCoinCounts');
        $machine = $this->machinePayload($payload);
        $insertedCoins = $this->objectPayload($machine, 'insertedCoins');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('money_returned', $event['type']);
        self::assertSame(1, $this->countValue($returnedCoinCounts, '0.10'));
        self::assertSame(1, $this->countValue($returnedCoinCounts, '0.25'));
        self::assertSame(0, $this->numberValue($machine, 'insertedBalanceCoins'));
        self::assertSame([], $insertedCoins);
    }

    #[Test]
    public function itServicesTheMachineThroughTheHttpInterface(): void
    {
        $this->seedDefaultMachine();

        $response = $this->request('POST', '/api/machine/service', [
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
        ]);
        $payload = $this->payload($response);
        $event = $this->eventPayload($payload);
        $machine = $this->machinePayload($payload);
        $availableChangeCounts = $this->objectPayload($machine, 'availableChangeCounts');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('machine_serviced', $event['type']);
        self::assertSame(6, $this->productQuantity($payload, 'water'));
        self::assertSame(7, $this->productQuantity($payload, 'juice'));
        self::assertSame(4, $this->productQuantity($payload, 'soda'));
        self::assertSame(4, $this->countValue($availableChangeCounts, '0.05'));
        self::assertSame(2, $this->countValue($availableChangeCounts, '1'));
    }

    #[Test]
    public function itReturnsConflictContextInCoinsThroughTheHttpInterface(): void
    {
        $this->seedDefaultMachine(DefaultMachineFixture::machine(
            availableChangeCounts: [],
            insertedCoinCounts: [100 => 3],
        ));

        $response = $this->request('POST', '/api/machine/select-product', ['selector' => 'soda']);
        $payload = $this->payload($response);
        $error = $this->errorPayload($payload);
        $context = $this->objectPayload($error, 'context');

        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
        self::assertSame('exact_change_unavailable', $error['code']);
        self::assertSame('Exact change cannot be returned.', $error['message']);
        self::assertSame(1.5, $this->numberValue($context, 'requiredChangeCoins'));
        self::assertSame('soda', $context['selector']);
    }

    #[Test]
    public function itMapsMachineNotFoundFailuresToJsonResponses(): void
    {
        $response = $this->request('GET', '/api/machine');
        $payload = $this->payload($response);
        $error = $this->errorPayload($payload);
        $context = $this->objectPayload($error, 'context');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame('machine_not_found', $error['code']);
        self::assertSame('default', $context['machineId']);
    }

    #[Test]
    public function itMapsTransportValidationErrorsToBadRequestResponses(): void
    {
        $this->seedDefaultMachine();

        $response = $this->request('POST', '/api/machine/insert-coin', ['coins' => 0.249]);
        $payload = $this->payload($response);
        $error = $this->errorPayload($payload);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame('invalid_request', $error['code']);
        self::assertSame('Field "coins" must be one of 0.05, 0.10, 0.25, or 1.', $error['message']);
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function request(string $method, string $uri, ?array $payload = null): Response
    {
        $content = $payload === null ? null : json_encode($payload, JSON_THROW_ON_ERROR);

        $request = Request::create(
            $uri,
            $method,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: $content,
        );

        return $this->httpKernel->handle($request);
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

    private function seedDefaultMachine(?Machine $machine = null): void
    {
        $this->machineRepository->save(MachineId::default(), $machine ?? DefaultMachineFixture::machine());
    }

    private function clearMachineCollection(): void
    {
        $this->machineCollection()->deleteMany([]);
    }

    private function machineCollection(): Collection
    {
        return $this->database->selectCollection(MongoDBMachineRepository::COLLECTION_NAME);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function machinePayload(array $payload): array
    {
        return $this->objectPayload($payload, 'machine');
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function eventPayload(array $payload): array
    {
        return $this->objectPayload($payload, 'event');
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function errorPayload(array $payload): array
    {
        return $this->objectPayload($payload, 'error');
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
    private function countValue(array $payload, string $field): int
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

    private function environmentVariable(string $name): string
    {
        $value = $_SERVER[$name] ?? getenv($name);

        if (!is_string($value) || $value === '') {
            self::fail(sprintf('Missing required environment variable "%s" for HTTP integration tests.', $name));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $machine
     *
     * @return list<array<string, mixed>>
     */
    private function productsPayload(array $machine): array
    {
        $products = $machine['products'] ?? null;

        self::assertIsArray($products);

        foreach ($products as $product) {
            self::assertIsArray($product);
        }

        /** @var list<array<string, mixed>> $products */
        return $products;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function productQuantity(array $payload, string $selector): int
    {
        foreach ($this->productsPayload($this->machinePayload($payload)) as $product) {
            if ($product['selector'] === $selector) {
                self::assertIsInt($product['quantity']);

                return $product['quantity'];
            }
        }

        self::fail(sprintf('Product "%s" was not found in the machine payload.', $selector));
    }
}
