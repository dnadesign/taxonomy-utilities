<?php

namespace DNADesign\Taxonomy\Utilities\Controllers;

use DNADesign\Taxonomy\Utilities\Search\TaxonomySearchIndex;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\FullTextSearch\Search\Criteria\SearchCriteria;
use SilverStripe\FullTextSearch\Search\Criteria\SearchCriterion;
use SilverStripe\FullTextSearch\Search\Queries\SearchQuery;
use SilverStripe\Taxonomy\TaxonomyTerm;

class TaggableSearchPageController extends Controller
{
    private static $allowed_actions = [
        'TagSearchForm'
    ];

    public function TagSearchForm()
    {
        $tags = TaxonomyTerm::get()->sort('ID ASC')->map('ID', 'Name')->toArray();
        array_walk($tags, function (&$item, $key) {
            return $item = sprintf('%s [%s]', $item, $key);
        });

        $fields = new FieldList([
            CheckboxSetField::create('tags', 'Tags', $tags)
        ]);
        $actions = new FieldList([
            FormAction::create('doFilter', 'Filter')
        ]);

        $form = new Form($this, 'TagSearchForm', $fields, $actions);
        $form->setFormMethod('GET');
        $form->disableSecurityToken();
        $form->loadDataFrom($this->getRequest()->getVars());

        return $form;
    }

    public function doFilter()
    {
        return $this;
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
        $limit = 10;
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
}
