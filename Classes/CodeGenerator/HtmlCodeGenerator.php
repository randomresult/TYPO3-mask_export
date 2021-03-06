<?php
namespace CPSIT\MaskExport\CodeGenerator;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Benjamin Butschell <bb@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use MASK\Mask\CodeGenerator\AbstractCodeGenerator;
use MASK\Mask\Helper\FieldHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generates the html and fluid for mask content elements
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class HtmlCodeGenerator extends AbstractCodeGenerator
{

    /**
     * Generates Fluid HTML for Contentelements
     *
     * @param string $key
     * @param string $table
     * @return string
     */
    public function generateHtml($key, $table = 'tt_content')
    {
        $storage = $this->storageRepository->loadElement('tt_content', $key);
        $html = '';
        if ($storage['tca']) {
            foreach ($storage['tca'] as $fieldKey => $fieldConfig) {
                $html .= $this->generateFieldHtml($fieldKey, $key);
            }
        }

        return $html;
    }

    /**
     * Generates HTML for a field
     *
     * @param string $fieldKey
     * @param string $elementKey
     * @param string $table
     * @param string $datafield
     * @return string
     */
    protected function generateFieldHtml($fieldKey, $elementKey, $table = 'tt_content', $datafield = 'data')
    {
        $html = '';
        $fieldHelper = GeneralUtility::makeInstance(FieldHelper::class);
        switch ($fieldHelper->getFormType($fieldKey, $elementKey, $table)) {
            case 'Check':
                $html .= <<<EOS
<f:if condition"{{$datafield}.{$fieldKey}}">
    <f:then>
        On<br />
    </f:then>
    <f:else>
        Off<br />
     </f:else>
</f:if>


EOS;
                break;

            case 'Content':
                $html .= <<<EOS
<f:if condition="{{$datafield}_{$fieldKey}}">
    <f:for each="{{$datafield}_{$fieldKey}}" as="content_item">
        <f:cObject typoscriptObjectPath="tt_content" data="{content_item.data}" /><br />
    </f:for>
</f:if>


EOS;
                break;

            case 'Date':
                $html .= <<<EOS
<f:if condition="{{$datafield}.{$fieldKey}}">
    <f:format.date format="d.m.Y">{{$datafield}.{$fieldKey}}</f:format.date><br />
</f:if>


EOS;
                break;

            case 'Datetime':
                $html .= <<<EOS
<f:if condition="{{$datafield}.{$fieldKey}}">
    <f:format.date format="d.m.Y - H:i:s">{{$datafield}.{$fieldKey}}</f:format.date><br />
</f:if>


EOS;
                break;

            case 'File':
                $html .= <<<EOS
<f:if condition="{{$datafield}_{$fieldKey}}">
    <f:for each="{{$datafield}_{$fieldKey}}" as="file">
        <f:image image="{file}" alt="{file.alternative}" title="{file.title}" width="200" /><br />
        {file.description} / {file.identifier}<br />
    </f:for>
</f:if>


EOS;
                break;

            case 'Float':
                $html .= <<<EOS
<f:if condition="{{$datafield}.{$fieldKey}}">
    <f:format.number decimals="2" decimalSeparator="," thousandsSeparator=".">{{$datafield}.{$fieldKey}}</f:format.number><br />
</f:if>


EOS;
                break;

            case 'Inline':
                $inlineFields = $this->storageRepository->loadInlineFields($fieldKey);
                $inlineFieldsHtml = '';
                $datafieldInline = strtr($datafield, '.', '_');
                if (!empty($inlineFields)) {
                    foreach ($inlineFields as $inlineField) {
                        $inlineFieldsHtml .= $this->generateFieldHtml($inlineField['maskKey'], $elementKey, $fieldKey, $datafieldInline . '_item.data');
                    }
                }
                $html .= <<<EOS
<f:if condition="{{$datafield}_{$fieldKey}}">
    <ul>
        <f:for each="{{$datafield}_{$fieldKey}}" as="{$datafieldInline}_item">
            <li>
                {$inlineFieldsHtml}
            </li>
        </f:for>
    </ul>
</f:if>


EOS;
                break;

            case 'Link':
                $html .= <<<EOS
<f:if condition="{{$datafield}.{$fieldKey}}">
    <f:link.page pageUid="{{$datafield}.{$fieldKey}}">{{$datafield}.{$fieldKey}}</f:link.page><br />
</f:if>


EOS;
                break;

            case 'Radio':
            case 'Select':
                $html .= <<<EOS
<f:if condition="{{$datafield}.{$fieldKey}}">
    <f:switch expression="{{$datafield}.{$fieldKey}}">
        <f:case value="1">Value is: 1</f:case>
        <f:case value="2">Value is: 2</f:case>
        <f:case value="3">Value is: 3</f:case>
    </f:switch><br />
</f:if>


EOS;
                break;

            case 'Richtext':
                $html .= <<<EOS
<f:if condition="{{$datafield}.{$fieldKey}}">
    <f:format.html parseFuncTSPath="lib.parseFunc_RTE">{{$datafield}.{$fieldKey}}</f:format.html><br />
</f:if>


EOS;
                break;

            case 'Text':
                $html .= <<<EOS
<f:if condition="{{$datafield}.{$fieldKey}}">
    <f:format.nl2br>{{$datafield}.{$fieldKey}}</f:format.nl2br><br />
</f:if>


EOS;
                break;

            default:
                $html .= <<<EOS
<f:if condition="{{$datafield}.{$fieldKey}}">
    {{$datafield}.{$fieldKey}}<br />
</f:if>


EOS;
                break;
        }

        return $html;
    }
}
