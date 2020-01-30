<?php

namespace DNADesign\Taxonomy\Utilities\Models;

use DNADesign\Taxonomy\Utilities\Controllers\TaxonomyReportController;
use DNADesign\Taxonomy\Utilities\Models\TaxonomySearchReport;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\GroupedList;
use SilverStripe\Taxonomy\TaxonomyTerm;

class TaxonomySearchReportEntry extends DataObject
{
    private static $table_name = 'TaxonomySearchReportEntry';

    private static $db = [
        'Signature' => 'Varchar(255)',
        'ResultCount' => 'Int'
    ];

    private static $has_one = [
        'Report' => TaxonomySearchReport::class
    ];

    private static $summary_fields = [
        'ID' => 'ID',
        'getReportedOn' => 'Reported On',
        'ResultCount' => 'Result Count',
        'getTagsSummary' => 'Tags',
        'getSearchLink' => 'View results'
    ];

    private static $default_sort = 'ResultCount DESC';

    /**
     * Return the list of TaxonomyTerms from the signature
     *
     * @return DataList
     */
    public function getTagList()
    {
        if ($this->Signature) {
            $ids = explode('+', $this->Signature);
            $tags = TaxonomyTerm::get()->filter('ID', $ids);

            return $tags;
        }

        return null;
    }

    /**
     * Return a formatted list of the TaxonomyTerms
     * recorded in the signature
     * grouped by TaxonomyType
     *
     * @return DBHTMLText
     */
    public function getTagsSummary()
    {
        $groupedTags = $this->getGroupedTags();
        if ($groupedTags) {
            $html = '<div>';
            foreach ($groupedTags as $type => $tagList) {
                $tags = array_map(function ($item) {
                    return sprintf('<span class="badge">%s</span>', $item);
                }, $tagList->column('Name'));
                $html .= sprintf('<div><strong>%s</strong>: %s</div>', $type, implode(' ', $tags));
            }

            $html .= '</div>';
            return DBHTMLText::create('Tags')->setValue($html);
        }
    }

    public function getGroupedTags()
    {
        $tags = $this->getTagList();
        if ($tags && $tags->exists()) {
            $list = GroupedList::create($tags->leftJoin('TaxonomyType', 'TaxonomyType.ID = TaxonomyTerm.TypeID')->sort('TaxonomyType.Name ASC, Name ASC'));
            return $list->groupBy('TaxonomyType');
        }
                
        return null;
    }

    /**
     * Generate a string to record the TaxonomyTerms
     * in one Varchar field
     *
     * @param array $tags
     * @return string
     */
    public static function generateSignature($tags = [])
    {
        if (!empty($tags)) {
            $tags = array_unique($tags);
            sort($tags);
            $signature = implode('+', $tags);

            return $signature;
        }

        return null;
    }

    /**
     * Check if a record with the same signature for the same report already exists in DB
     *
     * @return void
     */
    public function alreadyExists()
    {
        if ($this->Signature && $this->ResultCount && $this->ReportID) {
            return self::get()->filter(['Signature' => $this->Signature, 'ResultCount' => $this->ResultCount, 'ReportID' => $this->ReportID])->exists();
        }

        return false;
    }

    /**
     * Return the report Created date
     *
     * @return string
     */
    public function getReportedOn()
    {
        $report = $this->Report();
        if ($report &&  $report->exists()) {
            return $report->dbObject('Created');
        }
    }

    /**
     * Generate a link to this Entry for the report
     *
     * @return string
     */
    public function getSearchLink()
    {
        return Controller::join_links(Director::absoluteBaseURL(), 'taxonomysearchreport', 'list', $this->ID);
    }
}
