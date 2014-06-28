<?php
namespace W4Y\MultiPager\Tests;

use W4Y\MultiPager\Pager;
use W4Y\MultiPager\Source\MockSource;

class PagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Pager */
    private $pager;

    public function setUp()
    {
        $this->pager = new Pager;

        $options = ['query' => 'My query to search for in all sources'];

        // Results 80 total
        $this->pager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 1']), 25));
        $this->pager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 2']), 15));
        $this->pager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 3']), 13));
        $this->pager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 4']), 17));
        $this->pager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 5']), 10));
    }

    public function testCalculatePagination()
    {

        // With the set limit page 1 at position 26 should be Source 2 result 1.
        $this->pager->reset();
        $this->pager->setLimit(27);
        $dt = $this->pager->fetch(1);
        $pos = 26;
        $data = $dt[$pos - 1];
        $expectedDataString = $data['source'] . ' - ' . $data['data'];
        $this->assertEquals('Source 2 - result_1', $expectedDataString);

        // With the set limit page 2 at position 1 should be Source 2 result 3.
        $this->pager->reset();
        $this->pager->setLimit(27);
        $dt = $this->pager->fetch(2);
        $pos = 1;
        $data = $dt[$pos - 1];
        $expectedDataString = $data['source'] . ' - ' . $data['data'];
        $this->assertEquals('Source 2 - result_3', $expectedDataString);

        // With the set limit page 2 at position 1 should be Source 3 result 1.
        $this->pager->reset();
        $this->pager->setLimit(40);
        $dt = $this->pager->fetch(2);
        $pos = 1;
        $data = $dt[$pos - 1];
        $expectedDataString = $data['source'] . ' - ' . $data['data'];
        $this->assertEquals('Source 3 - result_1', $expectedDataString);

        // With the set limit page 2 at position 14 should be Source 4 result 1.
        $this->pager->reset();
        $this->pager->setLimit(40);
        $dt = $this->pager->fetch(2);
        $pos = 14;
        $data = $dt[$pos - 1];
        $expectedDataString = $data['source'] . ' - ' . $data['data'];
        $this->assertEquals('Source 4 - result_1', $expectedDataString);

        // With the set limit page 2 at position 31 should be Source 5 result 1.
        $this->pager->reset();
        $this->pager->setLimit(40);
        $dt2 = $this->pager->fetch(2);
        $pos = 31;
        $data = $dt2[$pos - 1];
        $expectedDataString = $data['source'] . ' - ' . $data['data'];
        $this->assertEquals('Source 5 - result_1', $expectedDataString);

        // With the set limit page 18 at position 3 should be Source 4 result 1.
        $this->pager->reset();
        $this->pager->setLimit(3);
        $dt2 = $this->pager->fetch(18);
        $pos = 3;
        $data = $dt2[$pos - 1];
        $expectedDataString = $data['source'] . ' - ' . $data['data'];
        $this->assertEquals('Source 4 - result_1', $expectedDataString);

        // With the set limit page 18 at position 3 should be Source 4 result 1.
        $this->pager->reset();
        $this->pager->setLimit(3);
        $dt2 = $this->pager->fetch(19);
        $pos = 1;
        $data = $dt2[$pos - 1];
        $expectedDataString = $data['source'] . ' - ' . $data['data'];
        $this->assertEquals('Source 4 - result_2', $expectedDataString);
    }

    public function testTotalResults()
    {
        $totalResults = $this->pager->getTotalItems();
        $this->assertEquals(80, $totalResults);
    }

    public function testCalculateNumberOfPages()
    {
        // Assuming we have 80 total results

        // 40 pages
        $this->pager->reset();
        $this->pager->setLimit(1);
        $totalPages = $this->pager->getTotalPages();
        $this->assertEquals(80, $totalPages);

        // 8 pages
        $this->pager->reset();
        $this->pager->setLimit(10);
        $totalPages = $this->pager->getTotalPages();
        $this->assertEquals(8, $totalPages);

        // 4 pages
        $this->pager->reset();
        $this->pager->setLimit(20);
        $totalPages = $this->pager->getTotalPages();
        $this->assertEquals(4, $totalPages);

        // 27 pages
        $this->pager->reset();
        $this->pager->setLimit(3);
        $totalPages = $this->pager->getTotalPages();
        $this->assertEquals(27, $totalPages);

        // 2 pages
        $this->pager->reset();
        $this->pager->setLimit(40);
        $totalPages = $this->pager->getTotalPages();
        $this->assertEquals(2, $totalPages);
    }
}