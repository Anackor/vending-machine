<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Symfony\Controller\Api;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use VendingMachine\Infrastructure\Symfony\Controller\Api\MachineJsonRequestFactory;

final class MachineJsonRequestFactoryTest extends TestCase
{
    private MachineJsonRequestFactory $requestFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestFactory = new MachineJsonRequestFactory();
    }

    public function testItCreatesTheDefaultStateQuery(): void
    {
        $query = $this->requestFactory->createGetMachineStateQuery();

        self::assertSame('default', $query->machineId());
    }

    public function testItCreatesAnInsertCoinCommandFromJson(): void
    {
        $command = $this->requestFactory->createInsertCoinCommand(
            $this->jsonRequest(['coinCents' => 25]),
        );

        self::assertSame(25, $command->coinCents());
        self::assertSame('default', $command->machineId());
    }

    public function testItCreatesASelectProductCommandFromJson(): void
    {
        $command = $this->requestFactory->createSelectProductCommand(
            $this->jsonRequest(['selector' => 'water']),
        );

        self::assertSame('water', $command->selector());
        self::assertSame('default', $command->machineId());
    }

    public function testItCreatesAServiceMachineCommandFromJson(): void
    {
        $command = $this->requestFactory->createServiceMachineCommand(
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
        );

        self::assertSame(
            ['juice' => 7, 'soda' => 4, 'water' => 6],
            $command->productQuantities(),
        );
        self::assertSame(
            [5 => 4, 10 => 5, 25 => 6, 100 => 2],
            $command->availableChangeCounts(),
        );
    }

    public function testItCreatesTheDefaultReturnInsertedMoneyCommand(): void
    {
        $command = $this->requestFactory->createReturnInsertedMoneyCommand();

        self::assertSame('default', $command->machineId());
    }

    public function testItRejectsInvalidJsonBodies(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Request body must be a JSON object.');

        $this->requestFactory->createInsertCoinCommand(
            Request::create(
                '/api/machine/insert-coin',
                'POST',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: '[1]',
            ),
        );
    }

    public function testItRejectsMissingRequiredFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "coinCents" is required.');

        $this->requestFactory->createInsertCoinCommand(
            Request::create(
                '/api/machine',
                'POST',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: '{}',
            ),
        );
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
