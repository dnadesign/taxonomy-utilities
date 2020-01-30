<?php

namespace DNADesign\Taxonomy\Utilities\Tasks;

use DNADesign\Taxonomy\Utilities\Controllers\TaxonomyReportController;
use DNADesign\Taxonomy\Utilities\Models\TaxonomySearchReport;
use DNADesign\Taxonomy\Utilities\Models\TaxonomySearchReportEntry;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Taxonomy\TaxonomyTerm;

class GenerateSearchByTaxonomyReport extends BuildTask
{
    protected $title = 'Generate a search by taxonomy report';

    private static $segment = 'generatetaxonomysearchreport';

    private $threshold = 3;
    private $reportID = 0;

    public function run($request)
    {
        $tags = TaxonomyTerm::get()->sort('ID ASC');
        
        // Create report object to hold entries
        $report = new TaxonomySearchReport();
        $report->write();
        $this->reportID = $report->ID;

        // Search tag by tag
        foreach ($tags as $tag) {
            // Search with one Tag
            $this->doSearchForTags([$tag->ID]);
        }

        echo sprintf('Found %s search that would exceed the %s max result threshold.', $report->Entries()->count(), $this->threshold);
    }

    /**
     * Recursively check if a search facets would exceed the threshold
     * and create a report entry accordingly
     *
     * @param array $tags
     * @return void
     */
    public function doSearchForTags($tags = [])
    {
        $controller = new TaxonomyReportController();
        $results = $controller->getListOfResults($tags);

        $matches = $results->getField('Matches');
        $count = $matches->getTotalItems();

        if ($count >= $this->threshold) {
            $facets = $results->getField('FacetCounts')->find('Name', '_tags');
            foreach ($facets->Facets as $facet) {
                $tagID = $facet->Name;
                $count = $facet->Count;
                if ($facet->Count >= $this->threshold) {
                    array_push($tags, $tagID);
                    $this->recordOverThresholdSearch($tags, $count);
                }
            }
        }
    }

    /**
     * Record a search that has exceed the threshold
     * on a TaxonomySearchReportEntry object
     *
     * @param array $tags
     * @param int $count
     * @return void
     */
    public function recordOverThresholdSearch($tags, $count)
    {
        $entry = new TaxonomySearchReportEntry();
        $entry->Signature = TaxonomySearchReportEntry::generateSignature($tags);
        $entry->ResultCount = $count;
        $entry->ReportID = $this->reportID;

        if (!$entry->alreadyExists()) {
            $entry->write();
            echo sprintf('Searching by tag "%s" will return %s results %s', $entry->Signature, $entry->ResultCount, PHP_EOL);
        }
    }
}
