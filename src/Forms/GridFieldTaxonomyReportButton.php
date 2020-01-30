<?php

namespace DNADesign\Taxonomy\Utilities\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;

class GridFieldTaxonomyReportButton implements GridField_HTMLProvider
{

    /**
     * @param string $targetFragment The HTML fragment to write the button into
     */
    public function __construct($targetFragment = "after")
    {
        $this->targetFragment = $targetFragment;
    }

    /**
     * Place the export button in a <p> tag below the field
     *
     * @param GridField $gridField
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        // Build action button
        $link = Controller::join_links(Director::absolutebaseURL(), 'taxonomysearchreport');
        $classes = 'btn btn-secondary font-icon-chart-pie btn--icon-large';
        $button = sprintf('<a href="%s" target="_blank" class="%s">View report</a>', $link, $classes);

        return array(
            $this->targetFragment => $button
        );
    }
}
