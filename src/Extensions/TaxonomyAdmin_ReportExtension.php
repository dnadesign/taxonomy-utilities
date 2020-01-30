<?php

namespace DNADesign\Taxonomy\Utilities\Extensions;

use DNADesign\Taxonomy\Utilities\Forms\GridFieldTaxonomyReportButton;
use SilverStripe\Core\Extension;

class TaxonomyAdmin_ReportExtension extends Extension
{
    /**
     * Add a direct link to the reporting interface
     *
     * @return void
     */
    public function updateEditForm($form)
    {
        $class = str_replace('\\', '-', $this->owner->modelClass);
        $gridField = $form->Fields()->fieldByName($class);
        $button = new GridFieldTaxonomyReportButton('buttons-before-left');
        $gridField->getConfig()->addComponent($button);
    }
}
