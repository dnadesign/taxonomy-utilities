# Taxonomy Utilities

This module provides the means to add TaxonomyTerms to any DataObject and generate a report that list the number of results
a search limited to this TaxonomyTerms would return.

## Add taxonomy to DataObject
To do so, apply `DNADesign\Taxonomy\Utilities\Extensions\DataObject_TaxonomyExtension` to any subclass of DataObject.
For example, add to your config.yml:
````
SilverStripe\CMS\Model\SiteTree:
  extensions:
    - DNADesign\Taxonomy\Utilities\Extensions\DataObject_TaxonomyExtension
````
Run `dev/build?flush=1` and you will be able to add any TaxonomyTerm to any of the SiteTree records.

## Access report
To see the report, go to the CMS tab `Taxonomies` and click on `View report`.
This takes you the report interface, listing the search that would return more result than the desired threshold.

## Generate report
To generate the report, in the Report interface, clcik on `Refresh report` in the top left corner (not working yet).
Alternatively, run `dev/tasks/generatetaxonomysearchreport`;
In the future, this report should be generated via a queuedjob.


## TODO
- Make threshold configurable
- Handle versioned object
- Generate Report via queued job