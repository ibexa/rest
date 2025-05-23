<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;

class RelationTest extends RESTFunctionalTestCase
{
    public function testRelation(): void
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="/api/ibexa/v2/content/types/2" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="/api/ibexa/v2/content/locations/1/2" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/api/ibexa/v2/content/sections/1" />
  <alwaysAvailable>true</alwaysAvailable>
  <User href="/api/ibexa/v2/user/users/14" />
  <modificationDate>2012-09-30T12:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>testRelation</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>intro</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>
        <value key="xml">&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ibexa.co/xmlns/dxp/docbook/xhtml" xmlns:ezcustom="http://ibexa.co/xmlns/dxp/docbook/custom" version="5.0-variant ezpublish-1.0"&gt;
&lt;title ezxhtml:level="2"&gt;This is a title.&lt;/title&gt;
&lt;para&gt;&lt;link xlink:href="ezcontent://1" xml:id="id1" xlink:title="Content title" ezxhtml:class="linkClass5"&gt;Content name&lt;/link&gt;&lt;/para&gt;
&lt;ezembed xlink:href="ezcontent://1" view="line" xml:id="embed-id-2" ezxhtml:class="embedClass2" ezxhtml:align="right"/&gt;
&lt;/section&gt;</value>
      </fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>image</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>
        <value key="destinationContentId">1</value>
      </fieldValue>
    </field>
  </fields>
</ContentCreate>
XML;
        $testContent = $this->createContent($xml);
        $relations = $testContent['CurrentVersion']['Version']['Relations']['Relation'];

        $expectedRelationTypes = [
            'LINK',
            'EMBED',
            'ATTRIBUTE',
        ];

        $actualTypes = array_column($relations, 'RelationType');

        self::assertEqualsCanonicalizing($expectedRelationTypes, $actualTypes);
    }
}
