<?php

namespace DNADesign\Taxonomy\Utilities\Controllers;

use DNADesign\Taxonomy\Utilities\Models\TaxonomySearchReport;
use DNADesign\Taxonomy\Utilities\Models\TaxonomySearchReportEntry;
use DNADesign\Taxonomy\Utilities\Search\TaxonomySearchIndex;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\FullTextSearch\Search\Criteria\SearchCriteria;
use SilverStripe\FullTextSearch\Search\Criteria\SearchCriterion;
use SilverStripe\FullTextSearch\Search\Queries\SearchQuery;
use SilverStripe\ORM\DataObject;
use SilverStripe\Taxonomy\TaxonomyTerm;
use SilverStripe\View\ArrayData;

class TaxonomyReportController extends Controller
{
    protected $templates = [
        'index' => 'TaxonomyReport'
    ];

    private static $allowed_actions = [
        'list'
    ];

    // Action to list Objects returned by a search
    public function list()
    {
        $entry = TaxonomySearchReportEntry::get()->byID($this->getRequest()->latestParam('ID'));
        if ($entry) {
            $tags = $entry->getTagList();
            if ($tags) {
                return $this->customise(new Arraydata([
                    'Tags' => $tags,
                    'Objects' => $this->getListOfResults($tags->column('ID'))
                ]))->renderWith('TaxonomyReport_list');
            }
        }
        
        return $this->httpError(404);
    }

    public function getListOfResults($tagsParam = [])
    {
        $request = $this->getRequest();

        // Build query
        $query = SearchQuery::create();

        // Add tag filters
        $tags = !empty($tagsParam) ? $tagsParam : $request->requestVar('tags');
        if ($tags) {
            $criteria = SearchCriteria::create(SearchCriterion::create('_tags', array_shift($tags), SearchCriterion::EQUAL));
            if (count($tags) > 0) {
                foreach ($tags as $tagID) {
                    $criteria->addAnd(SearchCriteria::create(SearchCriterion::create('_tags', $tagID, SearchCriterion::EQUAL)));
                }
            }
            $query->filterBy($criteria);
        }

        // Limit search
        $limit = 20;
        $offset = $request->getVar('start');

        // Extra params
        // Disabled for now. Facets might be used later on
        $params = [
            'facet' => 'true',
            'facet.field' => '_tags'
        ];

        // Get the first index
        $indexClass = TaxonomySearchIndex::class;

        /** @var SolrIndex $index */
        $index = $indexClass::singleton();
        $results = $index->search($query, $offset, $limit, $params);

        return $results;
    }

    /**
     * Retrieve the list of classes that implements the DataObject_TaxonomyExtension
     *
     * @return array
     */
    public static function get_classes_to_index()
    {
        $classes = ClassInfo::subclassesFor(DataObject::class);
        $classes = array_filter($classes, function ($class) {
            return ClassInfo::hasMethod(singleton($class), 'IsTagged');
        });
        
        return $classes;
    }

    /**
     * Return the Report Entries from the last TaxonomySearchReport
     *
     * @return DataList
     */
    public function getLatestReportEntries()
    {
        $report = TaxonomySearchReport::get()->Last();
        if ($report && $report->exists()) {
            return $report->Entries();
        }
    }
}
