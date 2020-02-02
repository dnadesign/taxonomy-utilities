<?php

namespace DNADesign\Taxonomy\Utilities\Tasks;

use DNADesign\Taxonomy\Utilities\Controllers\TaxonomyReportController;
use DNADesign\Taxonomy\Utilities\Models\TaxonomySearchReport;
use DNADesign\Taxonomy\Utilities\Models\TaxonomySearchReportEntry;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\Queries\SQLDelete;
use SilverStripe\Taxonomy\TaxonomyTerm;

class GenerateSearchByTaxonomyReport extends BuildTask
{
    protected $title = 'Generate a search by taxonomy report';

    private static $segment = 'generatetaxonomysearchreport';

    private static $threshold = 2;

    private $reportID = 0;

    private $debug = false;

    public function run($request)
    {
        $tags = TaxonomyTerm::get()->sort('ID ASC');
       
        // Create report object to hold entries
        $report = new TaxonomySearchReport();
        $this->debug ?: $report->write();
        $this->reportID = $report->ID;

        // Search tag by tag
        foreach ($tags as $tag) {
            // Search with one Tag
            $this->doSearchForTags([$tag->ID]);
        }

        echo sprintf('Found %s search that would exceed the %s max result threshold with no more further filters available.', $report->Entries()->count(), $this->config()->threshold);
    }

    /**
     * Recursively check if a search facets would exceed the threshold
     * and create a report entry accordingly
     * Creating the report will trigger another search with the combined tags ID
     *
     * @param array $tags
     * @return void
     */
    public function doSearchForTags($tags = [])
    {
        echo sprintf('Search on tags %s %s', implode('|', $tags), PHP_EOL);

        $controller = new TaxonomyReportController();
        $results = $controller->getListOfResults($tags);

        $matches = $results->getField('Matches');
        $count = $matches->getTotalItems();

        if ($count >= $this->config()->threshold) {
            $facets = $results->getField('FacetCounts')->find('Name', '_tags');
            $facetResults = array_combine($facets->Facets->column('Name'), $facets->Facets->column('Count'));
            foreach ($tags as $toRemove) {
                if (isset($facetResults[$toRemove])) {
                    unset($facetResults[$toRemove]);
                }
            }
            $nextFacetSearchCount = array_sum(array_values($facetResults));

            // If there isn't any more facetted search results
            // And this search is exceeding the threshold
            // Then report it
            if ($nextFacetSearchCount == 0) {
                $this->recordOverThresholdSearch($tags, $count);
            } else {
                foreach ($facetResults as $tagID => $count) {
                    if ($count >= $this->config()->threshold) {
                        // Need to remove duplicate ID as the facets also return the original ID
                        // which then triggers an infinite loop
                        $searchedTags = array_unique(array_merge($tags, [$tagID]));
                        if (count($searchedTags) > 1) {
                            $this->doSearchForTags($searchedTags);
                        }
                    }
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
        $entry->TagCount = count($tags);

        if (!$entry->alreadyExists()) {
            // Remove searches with less tags than this search
            $lessSpecificSearches = TaxonomySearchReportEntry::get()->filter([
                'ReportID' => $this->reportID,
                'Signature:StartsWith' => $entry->Signature,
                'TagCount:LessThan' => $entry->TagCount
            ]);

            $this->debug ?: $entry->write();
            echo sprintf('Searching by tag "%s" will return %s results %s', $entry->Signature, $entry->ResultCount, PHP_EOL);
        }
    }

    /**
     * Remove every entry that is less specific that the most specific search possible
     * Reported to be over the threshold which therefore cannot be further refined.
     *
     * @param TaxonomySearchReport $report
     * @return void
     */
    public function optimiseReport($report)
    {
        $prevSignatures  = [];
        $maxTag = $report->Entries()->max('TagCount');
        for ($i = $maxTag; $i > 0; $i--) {
            $signatures = $report->Entries()->filter('TagCount', $i)->column('Signature');
            foreach ($signatures as $signature) {
                $ids = explode('+', $signature);
                for ($j = 0; $j < count($ids)-1; $j++) {
                    $prevSignature = (isset($prevSignatures[$j - 1])) ? $prevSignatures[$j - 1].'+'.$ids[$j] : $ids[$j];
                    array_push($prevSignatures, $prevSignature);
                }
            }
        }

        $lessSpecificSearches = TaxonomySearchReportEntry::get()->filter([
            'ReportID' => $report->ID,
            'Signature' => $prevSignatures
        ]);
        
        if ($lessSpecificSearches->count() > 0) {
            $where = sprintf('ID IN (%s)', implode(',', $lessSpecificSearches->column('ID')));
            $query = SQLDelete::create('TaxonomySearchReportEntry', $where);
            $query->execute();
        }
    }
}
