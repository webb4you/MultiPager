<?php
require dirname(__DIR__) . '/vendor/autoload.php';
use W4Y\MultiPager\Pager as Manager;
use W4Y\MultiPager\Source\MockSource;

$itemsPerPage = 15;
$manager = new Manager();
$options = ['query' => 'My Query'];


$manager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 1']), 25));
$manager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 2']), 15));
$manager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 3']), 13));
$manager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 4']), 17));
$manager->setDataSource(new MockSource(array_merge($options, ['name' => 'Source 5']), 10));

$manager->setLimit($itemsPerPage);

echo sprintf('Total Items: %d - Items per page: %d - Total pages: %d <br>',
    $manager->getTotalItems(),
    $itemsPerPage,
    $manager->getTotalPages()
);

for ($x = 0; $x < 1; $x++) {

    for ($i = 1; $i < $manager->getTotalPages() + 1; $i++) {

        $data = $manager->fetch($i);
        echo '<br>PAGE: ' . $i . '<br>';
        foreach ($data as $k => $v) {
            echo '&nbsp;&nbsp;' . ($k + 1) . ' - &nbsp;&nbsp;';
            echo sprintf('%s - %s<br>', $v['source'], $v['data']);
        }

    }

}