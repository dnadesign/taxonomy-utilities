<?php

namespace DNADesign\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class FacetedSearchExtension extends Extension
{
    /**
     * Adds extra information from the solr-php-client repsonse
     * into our search results.
     * @param ArrayData $results The ArrayData that will be used to generate search
     *        results pages.
     * @param stdClass $response The solr-php-client response object.
     */
    public function updateSearchResults($results, $response)
    {
        if (!isset($response->facet_counts) || !isset($response->facet_counts->facet_fields)) {
            return;
        }
        $facetCounts = ArrayList::create([]);
        foreach ($response->facet_counts->facet_fields as $name => $facets) {
            $facetDetails = ArrayData::create([
                'Name' => $name,
                'Facets' => ArrayList::create([]),
            ]);

            foreach ($facets as $facetName => $facetCount) {
                $facetDetails->Facets->push(ArrayData::create([
                    'Name' => $facetName,
                    'Count' => $facetCount,
                ]));
            }
            $facetCounts->push($facetDetails);
        }
        $results->setField('FacetCounts', $facetCounts);
    }
}
