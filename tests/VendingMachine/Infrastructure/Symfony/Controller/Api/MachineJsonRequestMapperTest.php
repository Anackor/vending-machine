<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Symfony\Controller\Api;

use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use VendingMachine\Infrastructure\Symfony\Controller\Api\MachineJsonRequestMapper;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Request\CoinInputNormalizer;

final class MachineJsonRequestMapperTest extends TestCase
{
    private MachineJsonRequestMapper $requestMapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestMapper = new MachineJsonRequestMapper(new CoinInputNormalizer());
    }

    public function testItCreatesTheDefaultStateQuery(): void
    {
        $query = $this->requestMapper->createGetMachineStateQuery();

        self::assertSame('default', $query->machineId()->value());
    }

    public function testItCreatesAnInsertCoinCommandFromJson(): void
    {
        $command = $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => 0.25]),
        );

        self::assertSame(25, $command->coinCents());
        self::assertSame('default', $command->machineId()->value());
    }

    public function testItStillCreatesAnInsertCoinCommandFromCoinCentsJson(): void
    {
        $command = $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coinCents' => 25]),
        );

        self::assertSame(25, $command->coinCents());
        self::assertSame('default', $command->machineId()->value());
    }

    public function testItAcceptsTheDocumentedDecimalCoinValues(): void
    {
        $command = $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => 0.1]),
        );

        self::assertSame(10, $command->coinCents());
    }

    public function testItAcceptsTheSmallestDocumentedDecimalCoinValue(): void
    {
        $command = $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => 0.05]),
        );

        self::assertSame(5, $command->coinCents());
    }

    public function testItAcceptsNumericStringCoins(): void
    {
        $command = $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => ' 0.25 ']),
        );

        self::assertSame(25, $command->coinCents());
    }

    public function testItAcceptsWholeCoinNumericStringValues(): void
    {
        $command = $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => '1.00']),
        );

        self::assertSame(100, $command->coinCents());
    }

    public function testItAcceptsOtherDocumentedNumericStringCoinValues(): void
    {
        $nickelCommand = $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => '0.05']),
        );
        $dimeCommand = $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => '0.10']),
        );

        self::assertSame(5, $nickelCommand->coinCents());
        self::assertSame(10, $dimeCommand->coinCents());
    }

    public function testItRejectsUnsupportedFractionalCoinAmounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "coins" must be one of 0.05, 0.10, 0.25, or 1.');

        $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => 0.249]),
        );
    }

    public function testItRejectsUnsupportedNumericStringCoinAmounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "coins" must be one of 0.05, 0.10, 0.25, or 1.');

        $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => '0.249']),
        );
    }

    public function testItCreatesASelectProductCommandFromJson(): void
    {
        $command = $this->requestMapper->createSelectProductCommand(
            $this->jsonRequest(['selector' => 'water']),
        );

        self::assertSame('water', $command->selector()->value());
        self::assertSame('default', $command->machineId()->value());
    }

    public function testItCreatesAServiceMachineCommandFromCoinsJson(): void
    {
        $command = $this->requestMapper->createServiceMachineCommand(
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
        );

        $productQuantities = $command->productQuantities();

        self::assertSame(
            ['juice', 'soda', 'water'],
            array_keys($productQuantities),
        );
        self::assertSame(7, $productQuantities['juice']->value());
        self::assertSame(4, $productQuantities['soda']->value());
        self::assertSame(6, $productQuantities['water']->value());
        self::assertSame(
            [5 => 4, 10 => 5, 25 => 6, 100 => 2],
            $command->availableChangeCounts(),
        );
    }

    public function testItStillCreatesAServiceMachineCommandFromCoinCentsJsonForCompatibility(): void
    {
        $command = $this->requestMapper->createServiceMachineCommand(
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

        self::assertSame([5 => 4, 10 => 5, 25 => 6, 100 => 2], $command->availableChangeCounts());
    }

    public function testItAcceptsOneDecimalCoinKeysForServiceChangeCounts(): void
    {
        $command = $this->requestMapper->createServiceMachineCommand(
            $this->jsonRequest([
                'productQuantities' => [
                    'water' => 6,
                    'juice' => 7,
                    'soda' => 4,
                ],
                'availableChangeCounts' => [
                    '0.1' => 5,
                ],
            ]),
        );

        self::assertSame([10 => 5], $command->availableChangeCounts());
    }

    public function testItCreatesTheDefaultReturnInsertedMoneyCommand(): void
    {
        $command = $this->requestMapper->createReturnInsertedMoneyCommand();

        self::assertSame('default', $command->machineId()->value());
    }

    public function testItRejectsInvalidJsonBodies(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Request body must be a JSON object.');

        $this->requestMapper->createInsertCoinCommand(
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
        $this->expectExceptionMessage('Field "coins" or "coinCents" is required.');

        $this->requestMapper->createInsertCoinCommand(
            Request::create(
                '/api/machine',
                'POST',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: '{}',
            ),
        );
    }

    public function testItRejectsMissingSelectorFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "selector" is required.');

        $this->requestMapper->createSelectProductCommand(
            Request::create(
                '/api/machine',
                'POST',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: '{}',
            ),
        );
    }

    public function testItRejectsMissingServiceObjectFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "productQuantities" is required.');

        $this->requestMapper->createServiceMachineCommand(
            Request::create(
                '/api/machine',
                'POST',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: '{}',
            ),
        );
    }

    public function testItRejectsEmptyJsonBodiesForInsertCoin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "coins" or "coinCents" is required.');

        $this->requestMapper->createInsertCoinCommand(
            Request::create(
                '/api/machine',
                'POST',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: '',
            ),
        );
    }

    public function testItRejectsNonNumericCoinsFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "coins" must be numeric.');

        $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => 'quarter']),
        );
    }

    public function testItRejectsNonScalarCoinsFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "coins" must be numeric.');

        $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coins' => ['quarter']]),
        );
    }

    public function testItRejectsNonIntegerCoinCentsFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "coinCents" must be an integer.');

        $this->requestMapper->createInsertCoinCommand(
            $this->jsonRequest(['coinCents' => '25']),
        );
    }

    public function testItRejectsNonStringSelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "selector" must be a string.');

        $this->requestMapper->createSelectProductCommand(
            $this->jsonRequest(['selector' => 10]),
        );
    }

    public function testItRejectsNonObjectServiceFields(): void
    {
        try {
            $this->requestMapper->createServiceMachineCommand(
                $this->jsonRequest([
                    'productQuantities' => [1, 2, 3],
                    'availableChangeCounts' => [],
                ]),
            );
            self::fail('The request mapper should reject list-based product quantities.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Field "productQuantities" must be a JSON object.', $exception->getMessage());
        }

        try {
            $this->requestMapper->createServiceMachineCommand(
                $this->jsonRequest([
                    'productQuantities' => ['water' => 1],
                    'availableChangeCounts' => [5, 10],
                ]),
            );
            self::fail('The request mapper should reject list-based available change counts.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Field "availableChangeCounts" must be a JSON object.', $exception->getMessage());
        }
    }

    public function testItRejectsEmptyAvailableChangeDenominationKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Available change denomination keys cannot be empty.');

        $this->requestMapper->createServiceMachineCommand(
            $this->jsonRequest([
                'productQuantities' => ['water' => 1],
                'availableChangeCounts' => ['' => 1],
            ]),
        );
    }

    public function testItRejectsInexactDecimalAvailableChangeDenominationKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Available change denomination keys must represent exact coin values.');

        $this->requestMapper->createServiceMachineCommand(
            $this->jsonRequest([
                'productQuantities' => ['water' => 1],
                'availableChangeCounts' => ['0.005' => 1],
            ]),
        );
    }

    public function testItRejectsNonNumericAvailableChangeDenominationKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Available change denomination keys must be numeric.');

        $this->requestMapper->createServiceMachineCommand(
            $this->jsonRequest([
                'productQuantities' => ['water' => 1],
                'availableChangeCounts' => ['quarter' => 1],
            ]),
        );
    }

    public function testItRejectsMalformedJsonBodies(): void
    {
        $this->expectException(JsonException::class);

        $this->requestMapper->createInsertCoinCommand(
            Request::create(
                '/api/machine',
                'POST',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: '{"coins":',
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
