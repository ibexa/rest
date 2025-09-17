<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Input\Handler;

use Ibexa\Contracts\Rest\Exceptions\Parser as ParserException;
use Ibexa\Contracts\Rest\Input\Handler;

/**
 * Input format handler base class.
 */
class Json extends Handler
{
    public function convert(string $string): array
    {
        $json = json_decode($string, true);
        if (JSON_ERROR_NONE !== ($jsonErrorCode = json_last_error())) {
            $message = "An error occured while decoding the JSON input:\n";
            $message .= $this->jsonDecodeErrorMessage($jsonErrorCode);
            $message .= "\nInput JSON:\n\n" . $string;
            throw new ParserException($message);
        }

        return $json;
    }

    /**
     * Returns the error message associated with the $jsonErrorCode.
     */
    private function jsonDecodeErrorMessage(int $jsonErrorCode): string
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }
        switch ($jsonErrorCode) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
        }

        return 'Unknown JSON decode error';
    }
}
