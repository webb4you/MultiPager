<?php
namespace W4Y\MultiPager\Source;


class MockSource extends DataSource
{
    /** @var null  */
    private $data = null;

    /** @var int  */
    private $itemCount = 10;

    public function __construct(array $options, $count = 10)
    {
        if (!empty($options['name'])) {
            $this->setName($options['name']);
        }

        $this->itemCount = $count;
        $this->getData();
        parent::__construct($options);
    }

    /**
     * Get the total number of results
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     *
     * @param $limit
     * @param int $offset
     * @return array
     */
    public function fetch($limit, $offset = 0)
    {
        $options = $this->getOptions();

        // Fetch a result set based on the given options.
        // Because this is a mock source return the data based
        // on the given limit/offset.
        return array_slice($this->getData(), $offset, $limit);
    }

    /**
     *
     * @return array|null
     */
    public function getData()
    {
        if (null !== $this->data) {
            return $this->data;
        }

        $dt = array();
        for ($i = 0; $i < $this->itemCount; $i++) {
            $dt[$i]['source'] = $this->getName();
            $dt[$i]['data'] = 'result_' . ($i + 1);
        }

        return $this->data = $dt;
    }
}