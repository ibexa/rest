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
     */
    protected array $mapping = [];

    /**
     * Adds view handler.
     */
    public function addVisitor(string $regexp, OutputVisitor $visitor): void
    {
        $this->mapping[$regexp] = $visitor;
    }

    /**
     * Dispatches a visitable result to the mapped visitor.
     *
     * @throws \RuntimeException
     */
    public function dispatch(Request $request, mixed $result): Response
    {
        foreach ($request->getAcceptableContentTypes() as $mimeType) {
            /** @var \Ibexa\Contracts\Rest\Output\Visitor $visitor */
            foreach ($this->mapping as $regexp => $visitor) {
                if (preg_match($regexp, $mimeType)) {
                    return $visitor->visit($result);
                }
            }
        }

        throw new RuntimeException('No view mapping found.');
    }
}
