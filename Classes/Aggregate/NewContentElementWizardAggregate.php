<?php
namespace CPSIT\MaskExport\Aggregate;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
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

/**
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class NewContentElementWizardAggregate extends AbstractAggregate implements LanguageAwareInterface, PlainTextFileAwareInterface, PhpAwareInterface
{
    use LanguageAwareTrait;
    use PlainTextFileAwareTrait;
    use PhpAwareTrait;

    /**
     * @var string
     */
    protected $languageFileIdentifier = 'locallang_db_new_content_el.xlf';

    /**
     * @var string
     */
    protected $languageFilePath = 'Resources/Private/Language/';

    /**
     * @var string
     */
    protected $pageTSConfigFileIdentifier = 'NewContentElementWizard.ts';

    /**
     * @var string
     */
    protected $pageTSConfigFilePath = 'Configuration/PageTSconfig/';

    /**
     * Adds content elements to the newContentElementWizard
     */
    protected function process()
    {
        if (empty($this->maskConfiguration['tt_content']['elements'])) {
            return;
        }

        $this->appendPlainTextFile(
            $this->pageTSConfigFilePath . $this->pageTSConfigFileIdentifier,
<<<EOS
mod.wizards.newContentElement.wizardItems.common {
    elements {

EOS
        );

        foreach ($this->maskConfiguration['tt_content']['elements'] as $element) {
            $this->processElement($element);
        }

        $elementKeys = implode(', ', array_keys($this->maskConfiguration['tt_content']['elements']));
        $this->appendPlainTextFile(
            $this->pageTSConfigFilePath. $this->pageTSConfigFileIdentifier,
<<<EOS
    }
    show := addToList({$elementKeys})
}

EOS
        );

        $this->addPhpFile(
            'ext_localconf.php',
<<<EOS
\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mask/{$this->pageTSConfigFilePath}{$this->pageTSConfigFileIdentifier}">'
);

EOS
        );
    }

    /**
     * @param array $element
     */
    protected function processElement(array $element)
    {
        $key = $element['key'];

        $this->addLabel(
            $this->languageFilePath . $this->languageFileIdentifier,
            'wizards.newContentElement.' . $key . '_title',
            (!empty($element['label'])) ? $element['label'] : $key
        );
        $this->addLabel(
            $this->languageFilePath . $this->languageFileIdentifier,
            'wizards.newContentElement.' . $key . '_description',
            (!empty($element['description'])) ? $element['description'] : ''
        );

        $this->appendPlainTextFile(
            $this->pageTSConfigFilePath . $this->pageTSConfigFileIdentifier,
<<<EOS
            {$key} {
                iconIdentifier = content-textpic
                title = LLL:EXT:mask/{$this->languageFilePath}{$this->languageFileIdentifier}:wizards.newContentElement.{$key}_title
                description = LLL:EXT:mask/{$this->languageFilePath}{$this->languageFileIdentifier}:wizards.newContentElement.{$key}_description
                tt_content_defValues {
                    CType = mask_{$key}
                }
            }

EOS
        );
    }
}
