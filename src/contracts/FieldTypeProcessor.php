<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest;

/**
 * FieldTypeProcessor.
 */
abstract class FieldTypeProcessor
{
    /**
     * Perform manipulations on a received $incomingValueHash.
     *
     * This method is called by the REST input parsers to allow a field
     * type to pre process the given $incomingValueHash before it is handled by
     * {@link Ibexa\Contracts\Core\FieldType\FieldType::fromHash()}. The
     * $incomingValueHash can be expected to conform to the rules that need to
     * apply to hashes accepted by fromHash(). The return value of this method
     * replaces the $incomingValueHash.
     *
     * @see \Ibexa\Rest\Input\FieldTypeParser
     *
     * @return mixed Pre processed hash
     */
    public function preProcessValueHash(mixed $incomingValueHash): mixed
    {
        return $incomingValueHash;
    }

    /**
     * Perform manipulations on an a generated $outgoingValueHash.
     *
     * This method is called by the REST output visitors to allow a field type to
     * post process the given $outgoingValueHash, which was previously generated
     * using {@link Ibexa\Contracts\Core\FieldType\FieldType::toHash()}, before it is
     * sent to the client. The return value of this method replaces
     * $outgoingValueHash and must obey to the same rules as the original
     * $outgoingValueHash.
     *
     * @see \Ibexa\Rest\Output\FieldTypeSerializer
     *
     * @return mixed Post processed hash
     */
    public function postProcessValueHash(mixed $outgoingValueHash): mixed
    {
        return $outgoingValueHash;
    }

    /**
     * Perform manipulations on a received $incomingSettingsHash.
     *
     * This method is called by the REST input parsers to allow a field type to
     * pre process the given $incomingSettingsHash before it is handled by
     * {@link Ibexa\Contracts\Core\FieldType\FieldType::fieldSettingsFromHash()}. The
     * $incomingSettingsHash can be expected to conform to the rules that
     * need to apply to hashes accepted by fieldSettingsFromHash(). The return
     * value of this method replaces the $incomingSettingsHash.
     *
     * @see \Ibexa\Rest\Input\FieldTypeParser
     *
     * @return mixed Preprocessed hash
     */
    public function preProcessFieldSettingsHash(mixed $incomingSettingsHash): mixed
    {
        return $incomingSettingsHash;
    }

    /**
     * Perform manipulations on a received $outgoingSettingsHash.
     *
     * This method is called by the REST output visitors to allow a field type to post
     * process the given $outgoingSettingsHash, which was previously generated
     * using {@link Ibexa\Contracts\Core\FieldType\FieldType::fieldSettingsToHash()},
     * before it is sent to the client. The return value of this method replaces
     * $outgoingSettingsHash and must obey to the same rules as the original
     * $outgoingSettingsHash.
     *
     * @see \Ibexa\Rest\Output\FieldTypeSerializer
     *
     * @return mixed Post processed hash
     */
    public function postProcessFieldSettingsHash(mixed $outgoingSettingsHash): mixed
    {
        return $outgoingSettingsHash;
    }

    /**
     * Perform manipulations on a received $incomingValidatorConfigurationHash.
     *
     * This method is called by the REST input parsers to allow a field type to pre
     * process the given $incomingValidatorConfigurationHash before it is handled
     * by {@link Ibexa\Contracts\Core\FieldType\FieldType::validatorConfigurationFromHash()}.
     * The $incomingValidatorConfigurationHash can be expected to conform to the
     * rules that need to apply to hashes accepted by validatorConfigurationFromHash().
     * The return value of this method replaces the $incomingValidatorConfigurationHash.
     *
     * @see \Ibexa\Rest\Input\FieldTypeParser
     *
     * @return mixed Preprocessed hash
     */
    public function preProcessValidatorConfigurationHash(mixed $incomingValidatorConfigurationHash): mixed
    {
        return $incomingValidatorConfigurationHash;
    }

    /**
     * Perform manipulations on a received $outgoingValidatorConfigurationHash.
     *
     * This method is called by the REST output visitors to allow a field type to post
     * process the given $outgoingValidatorConfigurationHash, which was previously generated
     * using {@link Ibexa\Contracts\Core\FieldType\FieldType::validatorConfigurationToHash()},
     * before it is sent to the client. The return value of this method replaces
     * $outgoingValidatorConfigurationHash and must obey to the same rules as the original
     * $outgoingValidatorConfigurationHash.
     *
     * @see \Ibexa\Rest\Output\FieldTypeSerializer
     *
     * @return mixed Post processed hash
     */
    public function postProcessValidatorConfigurationHash(mixed $outgoingValidatorConfigurationHash): mixed
    {
        return $outgoingValidatorConfigurationHash;
    }
}
