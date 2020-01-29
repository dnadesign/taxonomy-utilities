<?php

namespace DNADesign\Taxonomy\Utilities\Models;

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

    public function getTagList()
    {
        if ($this->Signature) {
            $ids = explode('+', $this->Signature);
            $tags = TaxonomyTerm::get()->filter('ID', $ids);

            return $tags;
        }

        return null;
    }

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

    public function alreadyExists()
    {
        if ($this->Signature && $this->ResultCount && $this->ReportID) {
            return self::get()->filter(['Signature' => $this->Signature, 'ResultCount' => $this->ResultCount, 'ReportID' => $this->ReportID])->exists();
        }

        return false;
    }

    public function getReportedOn()
    {
        $report = $this->Report();
        if ($report &&  $report->exists()) {
            return $report->dbObject('Created');
        }
    }

    public function getSearchLink()
    {
        $page = TaggableSearchPage::get()->First();
        if ($page) {
            $tags = $this->getTagList()->column('ID');
            array_walk($tags, function (&$item) {
                return $item = '?tags[]='.$item;
            });
            $pageLink = $page->Link() == '/' ? 'home' : $page->AbsoluteLink();
            $url = Controller::join_links(Director::absoluteBaseURL(), $pageLink, 'TagSearchForm', '?action_doFilter=Filter', implode('', $tags));
            $link = sprintf('<a href="%s" target="_blank">View results</a>', $url);
            return DBHTMLText::create('Tags')->setValue($link);
        }

        return null;
    }
}
