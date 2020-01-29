<?php

namespace DNADesign\Taxonomy\Utilities\Models;

use SilverStripe\ORM\DataObject;
use Taggable\TaggableReportEntry;

class TaxonomySearchReport extends DataObject
{
    private static $table_name = 'TaxonomySearchReport';

    private static $has_many = [
        'Entries' => TaxonomySearchReportEntry::class
    ];
}
