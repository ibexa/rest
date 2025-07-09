<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo as VersionInfoValue;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Core\Repository\Values;
use Ibexa\Rest\Server\Values\VersionTranslationInfo as VersionTranslationInfoValue;
use RuntimeException;

/**
 * VersionInfo value object visitor.
 */
class VersionInfo extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startHashElement('VersionInfo');
        $this->visitVersionInfoAttributes($visitor, $generator, $data);
        $generator->endHashElement('VersionInfo');
    }

    /**
     * Maps the given version $status to a representative string.
     */
    protected function getStatusString(int $status): string
    {
        switch ($status) {
            case Values\Content\VersionInfo::STATUS_DRAFT:
                return 'DRAFT';

            case Values\Content\VersionInfo::STATUS_PUBLISHED:
                return 'PUBLISHED';

            case Values\Content\VersionInfo::STATUS_ARCHIVED:
                return 'ARCHIVED';
        }

        throw new RuntimeException('Undefined version status: ' . $status);
    }

    protected function visitVersionInfoAttributes(Visitor $visitor, Generator $generator, VersionInfoValue $versionInfo): void
    {
        $generator->startValueElement('id', $versionInfo->id);
        $generator->endValueElement('id');

        $generator->startValueElement('versionNo', $versionInfo->versionNo);
        $generator->endValueElement('versionNo');

        $generator->startValueElement(
            'status',
            $this->getStatusString($versionInfo->status)
        );
        $generator->endValueElement('status');

        $generator->startValueElement(
            'modificationDate',
            $versionInfo->modificationDate->format('c')
        );
        $generator->endValueElement('modificationDate');

        $generator->startObjectElement('Creator', 'User');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_user',
                ['userId' => $versionInfo->creatorId]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Creator');

        $generator->startValueElement(
            'creationDate',
            $versionInfo->creationDate->format('c')
        );
        $generator->endValueElement('creationDate');

        $generator->startValueElement(
            'initialLanguageCode',
            $versionInfo->initialLanguageCode
        );
        $generator->endValueElement('initialLanguageCode');

        $generator->startValueElement(
            'languageCodes',
            implode(',', $versionInfo->languageCodes)
        );
        $generator->endValueElement('languageCodes');

        $visitor->visitValueObject(new VersionTranslationInfoValue($versionInfo));

        $this->visitNamesList($generator, $versionInfo->names);

        $generator->startObjectElement('Content', 'ContentInfo');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_content', ['contentId' => $versionInfo->getContentInfo()->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Content');
    }
}
