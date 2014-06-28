<?php
namespace W4Y\MultiPager\Source;

/**
 * DataSource
 *
 * Class that fetches data based on a limit and offset
 * Must implement this class to use with a data manager.
 *
 */
abstract class DataSource
{
    /** @var array|null  */
    private $options = null;

    /** @var string|null  */
    private $name = null;

    public function __construct(array $options)
    {
        if (empty($options)) {
            throw new \Exception('Options must be set');
        }

        $this->options = $options;
    }

    /**
     * Get Options
     *
     * @return array|null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set name
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Return name
     *
     * @return null|string
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = get_class($this);
        }

        return $this->name;
    }

    /**
     * Count
     *
     * Return the count of the objects.
     */
    abstract public function count();

    /**
     * Fetch results based on limit and offset
     *
     * @param $limit
     * @param int $offset
     * @return array
     */
    abstract public function fetch($limit, $offset = 0);
}