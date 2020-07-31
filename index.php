<?php

require 'vendor/autoload.php';

use SolrGrouping\Solr;

$solr = new Solr();

if (!empty($_REQUEST['reset'])) {
    $solr->reset();
}

if (!empty($_REQUEST['push'])) {
    $solr->push();
}

$query = isset($_REQUEST['query']) ? $_REQUEST['query'] : null;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$results = $solr->search(--$page);

echo '<ul>';
foreach ($results as $result) {
    echo '<li>';
    if ($result['type_s'] == 'sector') {
        echo '<a href="#">' . $result['name_s'] . '</a>';
    } else {
        if (!empty($result['_expanded_'])) {
            echo '<span>' . $result['sector_title_s'] . '</span>';
        } else {
            echo '<span>Résultat sans sujet.</span>';
            echo '<ul><li>';
            echo '<a href="#">' . $result['location_s'] . ' (' . $result['status_s'] . ')' . ' #'
                . $result['id_s'] . '</a>';
            echo '</li></ul>';
        }
    }
    if (!empty($result['_expanded_'])) {
        echo '<ul>';
        foreach ($result['_expanded_'] as $subresult) {
            echo '<li><a href="#">' . $subresult['location_s'] . ' (' . $subresult['status_s'] . ')' . ' #'
                . $subresult['id_s'] . '</a></li>';
        }
        echo '</ul>';
    }
    echo '</li>';
}
echo '</ul>';

