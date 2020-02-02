<?php

namespace DNADesign\Taxonomy\Utilities\Controllers;

use DNADesign\Taxonomy\Utilities\Models\TaxonomySearchReport;
use DNADesign\Taxonomy\Utilities\Models\TaxonomySearchReportEntry;
use DNADesign\Taxonomy\Utilities\Search\TaxonomySearchIndex;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\FullTextSearch\Search\Criteria\SearchCriteria;
use SilverStripe\FullTextSearch\Search\Criteria\SearchCriterion;
use SilverStripe\FullTextSearch\Search\Queries\SearchQuery;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Taxonomy\TaxonomyTerm;
use SilverStripe\View\ArrayData;

class TaxonomyReportController extends Controller
{
    protected $templates = [
        'index' => 'TaxonomyReport',
        'search' => 'TaxonomyReport_search'
    ];

    private static $allowed_actions = [
        'list',
        'search'
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
                    'Results' => $this->getListOfResults($tags->column('ID')),
                    'LinkToSearch' => $this->getLinkToSearch($tags->column('ID'), $entry->ID)
                ]))->renderWith('TaxonomyReport_list');
            }
        }
        
        return $this->httpError(404);
    }

    /**
     * Action to perform a "manual" search based on TaxonomyTerms
     *
     * @return Controller
     */
    public function search($data = null, $request = null)
    {
        return $this;
    }

    /**
     * Return a form listing every TaxonomyTerm available
     * so to perform a search the same way the reporting task does
     *
     * @return Form
     */
    public function TaxonomySearchForm()
    {
        $fields = FieldList::create([
            CheckboxSetField::create('tags', '', TaxonomyTerm::get()->sort('Name ASC')->map()->toArray())
        ]);

        $actions = FieldList::create([
            FormAction::create('search', 'Search')
        ]);

        $form = new Form($this, 'TaxonomySearchForm', $fields, $actions);
        $form->setFormMethod('GET');
        $form->disableSecurityToken();
        $form->loadDataFrom($this->getRequest()->getVars());
        $form->setFormAction('taxonomysearchreport/search');

        return $form;
    }

    /**
     * Generate  a Paginated List from a Solr Search
     * looking for all instance of DataObject that have the TaxonomyExtension
     * and match the TaxonomyTerm supplied
     *
     * @param array $tagsParam
     * @return PaginatedList
     */
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
                    $criteria->addAnd(SearchCriterion::create('_tags', $tagID, SearchCriterion::EQUAL));
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
     * @return PaginatedList
     */
    public function getLatestReportEntries()
    {
        $report = TaxonomySearchReport::get()->Last();
        if ($report && $report->exists()) {
            $list = new PaginatedList($report->Entries(), $this->getRequest());
            $list->setPageLength(20);
            return $list;
        }
    }

    /**
     * Helper
     */
    public function getEntryNumber($start, $pos)
    {
        return (int) $start + (int) $pos;
    }

    public function getLinkToSearch($tags = [], $reportID)
    {
        if (!empty($tags)) {
            array_walk($tags, function (&$item) {
                return $item = '&tags[]='.$item;
            });
        }

        $url = Controller::join_links(Director::absoluteBaseURL(), 'taxonomysearchreport/search/TagSearchForm', '?action_search=Search'.implode('', $tags).'&referrer='.$reportID);
        return $url;
    }

    public function getReferrerID()
    {
        return $this->getRequest()->getVar('referrer');
    }
}
