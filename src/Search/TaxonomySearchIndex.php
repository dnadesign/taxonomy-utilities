<?php

namespace DNADesign\Taxonomy\Utilities\Search;

use Document;
use DNADesign\Extensions\FacetedSearchExtension;
use SilverStripe\FullTextSearch\Solr\SolrIndex;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

if (!class_exists(SolrIndex::class)) {
    return;
}

/**
 * Provides ability to index Elemental content for a page, so it can be returned in the context of the page
 * that the elements belong to
 */
class TaxonomySearchIndex extends SolrIndex
{
    private static $extensions = [
        FacetedSearchExtension::class
    ];

    public function init()
    {
        // Search all page types that are tagged
        $this->addClass(Document::class);
        
        // Add Tags
        $this->addFilterField('Tags.ID');
        $this->addFulltextField('Tags.Name');
    }

    /**
     * Overridden field definitions to define additional custom fields like sort
     * fields and additional functionality.
     *
     * @return string
     */
    public function getFieldDefinitions()
    {
        $xml = parent::getFieldDefinitions();
        $xml .= "\n\t\t<field name='_tags' type='string' indexed='true' stored='true' multiValued='true'/>";

        return $xml;
    }

    /**
    * Copy all tags ID into single field for querying
    */
    public function getCopyFieldDefinitions()
    {
        $xml = parent::getCopyFieldDefinitions();

        $xml .= "\n\t<copyField source='Document_Tags_ID' dest='_tags' />";

        return $xml;
    }
}
