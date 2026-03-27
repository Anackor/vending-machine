<?php

declare(strict_types=1);

namespace Tests\App;

use App\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class KernelTest extends TestCase
{
    public function testItBuildsTheSymfonyKernel(): void
    {
        $kernel = new Kernel('test', true);

        self::assertInstanceOf(BaseKernel::class, $kernel);
        self::assertSame('test', $kernel->getEnvironment());
        self::assertTrue($kernel->isDebug());
    }
}
