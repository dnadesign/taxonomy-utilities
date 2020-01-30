<?php

namespace DNADesign\Taxonomy\Utilities\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\GroupedList;
use SilverStripe\Taxonomy\TaxonomyTerm;

class DataObject_TaxonomyExtension extends DataExtension
{
    private static $many_many = [
        'Tags' => TaxonomyTerm::class
    ];

    /**
     * Provide interface to manage TaxonomyTerms
     * on a DataObject
     *
     * @param FieldList $fields
     * @return void
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Tags');

        $config = GridFieldConfig_RelationEditor::create();
        $tags = GridField::create('Tags', 'Tags', $this->owner->Tags(), $config);

        $fields->addFieldToTab('Root.Main', $tags);
    }

    /**
     * Display list of tags on gridfield summary
     *
     * @param array $fields
     * @return void
     */
    public function updateSummaryFields(&$fields)
    {
        $fields['getTagsSummary'] = 'Tags';
    }

    /**
    * Return the formatted list of tags
    * grouped by TaxonomyType
    *
    * @return HTMLField
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

    /**
     * Bulid a grouped list of this object tags
     *
     * @return array
     */
    public function getGroupedTags()
    {
        if ($this->owner->Tags()->exists()) {
            $list = GroupedList::create($this->owner->Tags()->leftJoin('TaxonomyType', 'TaxonomyType.ID = TaxonomyTerm.TypeID')->sort('TaxonomyType.Name ASC, Name ASC'));
            return $list->groupBy('TaxonomyType');
        }

        return null;
    }

    /**
     * Helper
     */
    public function IsTagged()
    {
        return true;
    }
}
