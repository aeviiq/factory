<?php declare(strict_types=1);

namespace Aeviiq\Factory\Tests;

use Aeviiq\Factory\AbstractServiceFactory;
use Aeviiq\Factory\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ServiceFactoryTest extends TestCase
{
    public function testGetTarget(): void
    {
        $factory = $this->createFactory();
        $this->assertSame(\Traversable::class, $factory->getTarget());
    }

    public function testGetTargetWithInvalidTarget(): void
    {
        $factory = $this->createInvalidFactory();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(\sprintf('The target for "%s" must be an existing interface. "%s" given.', \get_class($factory), 'foo'));
        $factory->getTarget();
    }

    public function testGetByFqn(): void
    {
        $mockedContainer = $this->createMock(ContainerInterface::class);
        $expected = new \stdClass();
        $mockedContainer->method('get')->willReturn($expected);
        $factory = $this->createFactory();
        $factory->register('some_service_id');
        $factory->setContainer($mockedContainer);
        $result = $factory->getByFqn(\stdClass::class);
        $this->assertSame($expected, $result);
    }

    public function testGetByFqnWithMissingFqn(): void
    {
        $factory = $this->createFactory();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(\sprintf('Unable to find the requested service in "%s".', \get_class($factory)));
        $factory->getByFqn(\stdClass::class);
    }

    public function testRegister(): void
    {
        $factory = $this->createFactory();
        $factory->register('service_id');
        $mockedContainer = $this->createMock(ContainerInterface::class);
        $factory->setContainer($mockedContainer);
        $mockedContainer->expects($this->once())->method('get')->willReturn(new \stdClass());
        $factory->getByFqn(\stdClass::class);
    }

    private function createInvalidFactory(): AbstractServiceFactory
    {
        return new class() extends AbstractServiceFactory
        {
            protected function getTargetInterface(): string
            {
                return 'foo';
            }
        };
    }

    private function createFactory(): AbstractServiceFactory
    {
        return new class() extends AbstractServiceFactory
        {
            protected function getTargetInterface(): string
            {
                return \Traversable::class;
            }
        };
    }
}
