<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\View;

use Ibexa\Contracts\Rest\Output\Visitor as OutputVisitor;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dispatcher for various visitors depending on the mime-type accept header.
 */
class AcceptHeaderVisitorDispatcher
{
    /**
     * Mapping of regular expressions matching the mime type accept headers to
     * view handlers.
     *
     * @var array
     */
    protected $mapping = [];

    /**
     * Adds view handler.
     *
     * @param string $regexp
     * @param OutputVisitor $visitor
     */
    public function addVisitor(
        $regexp,
        OutputVisitor $visitor
    ) {
        $this->mapping[$regexp] = $visitor;
    }

    /**
     * Dispatches a visitable result to the mapped visitor.
     *
     * @param Request $request
     * @param mixed $result
     *
     * @throws RuntimeException
     *
     * @return Response
     */
    public function dispatch(
        Request $request,
        $result
    ) {
        foreach ($request->getAcceptableContentTypes() as $mimeType) {
            /** @var OutputVisitor $visitor */
            foreach ($this->mapping as $regexp => $visitor) {
                if (preg_match($regexp, $mimeType)) {
                    return $visitor->visit($result);
                }
            }
        }

        throw new RuntimeException('No view mapping found.');
    }
}

class_alias(AcceptHeaderVisitorDispatcher::class, 'EzSystems\EzPlatformRest\Server\View\AcceptHeaderVisitorDispatcher');
