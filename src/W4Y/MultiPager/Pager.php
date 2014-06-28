<?php
namespace W4Y\MultiPager;

use W4Y\MultiPager\Source\DataSource;

/**
 * My DataPaginator Manager
 *
 * Class that accepts classes that extend the
 * My_DataPaginator_Source class and returns the results
 * of these classes paginated based on the defined limit per
 * request.
 *
 * @author Ilan Rivers <ilan.rivers@outlook.com>
 */
class Pager
{
    /** @var DataSource[] */
    private $dataSources = array();

    /** @var array */
    private $sourceInfo = array();

    private $totalCount = 0;

    private $limit = self::DEFAULT_LIMIT;

    private $activeSources = array();

    const DEFAULT_LIMIT = 10;

    public function __construct(array $options = array())
    {
        if (!empty($options['limit'])) {
            $this->setLimit($options['limit']);
        }
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Get the results limit
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set a data source
     *
     * @param DataSource $dataSource
     */
    public function setDataSource(DataSource $dataSource)
    {
        $this->dataSources[] = $dataSource;
    }

    /**
     * Get available data sources.
     *
     * @return array
     */
    public function getDataSources()
    {
        return $this->dataSources;
    }

    /**
     * Return the processed source information
     *
     * @return array
     */
    public function getSourceInfo()
    {
        if (empty($this->sourceInfo)) {
            $this->processSources();
        }

        return $this->sourceInfo;
    }

    /**
     * Set the active sources
     *
     * @param $sources
     */
    private function setActiveSources($sources)
    {
        $this->activeSources = $sources;
    }

    /**
     * Get current active sources for the requested page.
     *
     * @return null|array
     */
    public function getActiveSources()
    {
        return $this->activeSources;
    }

    /**
     * Fetch the results for the given page using only the
     * active sources.
     *
     * @param $page
     * @return array
     */
    public function fetch($page)
    {
        // Get active sources
        $activeSources = $this->determineActiveSources($page);

        // Set active sources for current page
        $this->setActiveSources($activeSources);

        // Loop through all active sources and fetch results
        $dataSources = $this->getDataSources();
        $data = array();
        foreach ($activeSources as $source) {
            $dataSource = $dataSources[$source['index']];
            $dt = $dataSource->fetch($source['limit'], $source['offset']);
            $data = array_merge($data, $dt);
        }

        return $data;
    }

    /**
     * Total number of pages
     *
     * @return int
     */
    public function getTotalPages()
    {
        $totalItems = $this->getTotalItems();
        if (empty($totalItems)) {
            return 0;
        }

        $limit = $this->getLimit();
        $totalPages = ceil($this->getTotalItems() / $limit);

        return (int) $totalPages;
    }

    /**
     * Get total items
     *
     * @return int
     */
    public function getTotalItems()
    {
        return $this->fetchCount();
    }

    /**
     * Determine which sources should be used for displaying results based
     * on the given page.
     *
     * @param $page
     * @return array
     */
    private function determineActiveSources($page)
    {
        $limit = $this->getLimit();

        $sourceInfo = $this->getSourceInfo();
        $sources = $sourceInfo['sources'];

        // Calculate the offsets and limits for the eligible sources
        $candidates = array();
        $previousSourceRemaining = 0;
        foreach ($sources as $source) {

            if ($page >= $source['pageStart'] && $page <= $source['pageEnd']) {

                $srcLimit = $limit;

                // First page for this source
                if ($source['pageStart'] == $page) {

                    // Source limit will not always be the same as limit because
                    // we might have to compensate for results that may be on the page
                    // from a previous source.
                    $srcLimit = (!empty($previousSourceRemaining))
                        ? $previousSourceRemaining
                        : $srcLimit;

                    $offset = 0;

                } else {

                    // Offset : Calculate the offset.
                    // ( ( Current Amount to show for this page ) -
                    //  ( Minus total that was already shown on first page for this source)
                    // ) - ( Minus the total amount from all previous sources)
                    $offset = ( ($page * $limit) - ($limit - $previousSourceRemaining) - $source['countBefore']);
                }

                $source['offset'] = $offset;
                $source['limit'] = $srcLimit;
                $candidates[] = $source;

                $previousSourceRemaining = $source['pageRemaining'];
            }
        }

        return $candidates;
    }

    /**
     * Get the total results of all sources.
     *
     * @return int
     */
    private function fetchCount()
    {
        if (empty($this->totalCount)) {
            $this->processSources();
        }

        return $this->totalCount;
    }

    /**
     * Process Sources
     *
     * Determine the total results and the count for each source so we know
     * how many pages will be needed to display all sources.
     */
    private function processSources()
    {
        $key = '';
        foreach ($this->getDataSources() as $k => $dt) {
            $ops = $dt->getOptions();
            $key .= md5(serialize($ops)) . '###';
        }

        // @todo Implement cacheing plugin to speed up consecutive page viewing.
        $sourcesCombinedUniqueKey = 'sources_' . md5($key);

        $cnt = 0;
        $s = array();
        foreach ($this->getDataSources() as $k => $dt) {

            // Get source total count
            $count = $dt->count();

            $s[$k]['index'] = $k;
            $s[$k]['count'] = $count;
            $s[$k]['name'] = $dt->getName();

            // Number of total count results from sources before this one.
            $s[$k]['countBefore'] = $cnt;
            $cnt += $count;

            $s[$k] = array_merge($s[$k], $this->calculatePageRange($s[$k]));
        }

        $info = array(
            'totalCount' => $cnt,
            'sources' => $s
        );

        $this->sourceInfo = $info;
        $this->totalCount = $info['totalCount'];
    }

    /**
     * Calculate the start page and the end page for a given
     * data source based on the data source count and the count
     * of previous results before the data source.
     *
     * @param $source
     * @return array
     */
    private function calculatePageRange($source)
    {
        // Get the limit
        $limit = $this->getLimit();

        $srcCnt  	= $source['count'];
        $prevCnt 	= $source['countBefore'];
        $combineCnt = $srcCnt + $prevCnt;
        $startPage	= 1;

        if ($prevCnt) {
            $startPage = floor($prevCnt / $limit) + 1;
        }

        $lastPage = ceil($combineCnt / $limit);
        $remaining = $combineCnt - ($lastPage * $limit);

        $source['pageStart'] = $startPage;
        $source['pageEnd'] = $lastPage;
        $source['pageRemaining'] = abs($remaining);

        return $source;
    }

    /**
     * Reset the core variables.
     */
    public function reset()
    {
        $this->sourceInfo = array();
        $this->activeSources = array();
        $this->limit = self::DEFAULT_LIMIT;
        $this->totalCount = 0;
    }
}