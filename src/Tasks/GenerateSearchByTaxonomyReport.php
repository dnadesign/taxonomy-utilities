<?php

namespace DNADesign\Taxonomy\Utilities\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\Taxonomy\TaxonomyTerm;
use Taggable\TaggableReport;
use Taggable\TaggableReportEntry;
use Taggable\TaggableSearchPageController;

class GenerateSearchByTaxonomyReport extends BuildTask
{
    private static $segment = 'taxonomy-search-report';

    private $threshold = 10;
    private $reportID = 0;

    public function run($request)
    {
        $docs = Document::get();
        $tags = TaxonomyTerm::get()->sort('ID ASC');
        $controller = new TaggableSearchPageController();
        
        $report = new TaggableReport();
        $report->write();
        $this->report = $report;

        foreach ($tags as $tag) {
            // Search with one Tag
            $this->doSearchForTags([$tag->ID]);
        }
    }

    public function doSearchForTags($tags = [])
    {
        $controller = new TaggableSearchPageController();
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

    public function recordOverThresholdSearch($tags, $count)
    {
        $entry = new TaggableReportEntry();
        $entry->Signature = TaggableReportEntry::generateSignature($tags);
        $entry->ResultCount = $count;
        $entry->ReportID = $this->report->ID;

        if (!$entry->alreadyExists()) {
            $entry->write();
            echo sprintf('Searching by tag "%s" will return %s results %s', $entry->Signature, $entry->ResultCount, PHP_EOL);
        }
    }
}
