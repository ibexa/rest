<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\CreatedRole;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreatedRole::class)]
final class CreatedRoleTest extends ValueObjectVisitorBaseTestCase
{
    public function testVisitRoleDraftPointsLocationToDraft(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleDraft = new User\RoleDraft(['innerRole' => new User\Role(['id' => 42, 'identifier' => 'some-role'])]);

        $this->addRouteExpectation('ibexa.rest.load_role_draft', ['roleId' => 42], '/user/roles/42/draft');
        $this->addRouteExpectation('ibexa.rest.load_policies', ['roleId' => 42], '/user/roles/42/policies');
        $this->addRouteExpectation('ibexa.rest.load_role_draft', ['roleId' => 42], '/user/roles/42/draft');

        $headers = [];
        $this->getVisitorMock()->expects($this->exactly(3))
            ->method('setHeader')
            ->willReturnCallback(static function (
                string $name,
                mixed $value
            ) use (&$headers): void {
                $headers[$name] = $value;
            });
        $this->getVisitorMock()->expects($this->once())
            ->method('setStatus')
            ->with(201);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            new Values\CreatedRole(['role' => new Values\RestRole($roleDraft)]),
        );

        self::assertSame('/user/roles/42/draft', $headers['Location'] ?? null);
    }

    protected function internalGetVisitor(): CreatedRole
    {
        return new CreatedRole();
    }
}
