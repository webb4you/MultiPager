MultiPager
===========

MultiPager is a simple class that can paginate results from multiple or different sources.
Imagine you have to query a database for results and you also fetch results from a REST API, paginating through
these different result sets can become a bit tricky.

If you are browsing page 1 which source should you fetch the results from?
If you have 10 results per page and the first source only has 5 results you then have to do some
calculation to determine how many results from the second source should be fetched to satisfy your 10
results per page.

With one or two sources this is simple, when you get more sources it becomes bit more challenging.
You do not want to request data from a data source if it is not needed on that specific page.

Determining the right source to fetch from for each page is what MultiPager does for you.

##### Background
This was created for a project where I needed to browse data sources from multiple websites and feeds.
I needed a simple easy way to add new data sources without worrying about calculating my paginating all again.
So this is how MultiPager was born. Something similar might already exists but after extensive googling (5 minutes) I
decided write a simple data source pager.

Installation
------------
Add the following to your composer.json file

	"require": {
		"webb4you/multi-pager": "1.0.*"
	}

Usage
-----

Use composer to download required dependencies.

Import MultiPager

	```php
	use \W4Y\MultiPager\Pager;
	
	...
	
	$pager = new Pager();
	
	// The query parameter is not required, this is a shared variable we use in
	// our data sources to search for the required results.
	$options = ['query' => 'PHP Frameworks'];
	
	// Get your Data Source objects.
	$mysqlResults = new MysqlDataSource($options);
	$restApiResults = new RestApiDataSource($options);
	$luceneResults = new LuceneDataSource($options);
	
	// Set the data sources
	$pager->setDataSource($mysqlResults);
	$pager->setDataSource($restApiResults);
	$pager->setDataSource($luceneResults);
	
	// Set the max results per page
	$pager->setLimit(10);
	
	// Finally fetch the results for a given page.
	$page = 3;
	$results = $pager->fetch($page);
	```

#### Data Sources

A data source is any class that extends \MultiPager\Source\DataSource
You must implement the fetch and the count methods.

Results must be fetched using an offset and limit parameter that is passed to the fetch method.
The count must simply return the total number of results found.

Example
-----

For quick example, install MultiPager and browse to the example directory to see how to implement and use MultiPager
with a MockData Source.

