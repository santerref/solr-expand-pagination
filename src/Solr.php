<?php

namespace SolrGrouping;

use Solarium\Client;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Solr
{
    private $data;

    private $solarium;

    public function __construct()
    {
        $this->data = new Data();
        $eventDispatcher = new EventDispatcher();
        $this->solarium = new Client([
            'endpoint' => [
                'localhost' => [
                    'host' => 'solr',
                    'port' => '8983',
                    'path' => '/',
                    'core' => 'lando',
                ],
            ],
        ], $eventDispatcher);
    }

    public function reset()
    {
        $update = $this->solarium->createUpdate();
        $update->addDeleteQuery('*:*');
        $update->addCommit();
        $this->solarium->update($update);
    }

    public function push()
    {
        $this->reset();
        $data = $this->data->getOffers();

        $update = $this->solarium->createUpdate();

        $documents = [];
        foreach ($data['sectors'] as $sector) {
            $document = $update->createDocument($sector);
            $documents[] = $document;
        }

        foreach ($data['jobs'] as $job) {
            $document = $update->createDocument($job);
            $documents[] = $document;
        }

        if (!empty($documents)) {
            $update->addDocuments($documents);
            $update->addCommit();
            $this->solarium->update($update);
        }
    }

    public function search($page = 0)
    {
        $items = [];

        $select = $this->solarium->createSelect();
        $select->setQuery('statuses:*Permanent* OR location_s:*Finance* OR id_s:IRC133893');
        $select->addFields(['*', 'score']);
        $select->addParam('fq', "{!collapse field=sector_s sort='if(eq(field(type_s),\"sector\"),1,0) desc,score desc'}");
        $select->addParam('expand', 'true');
        $select->addParam('expand.rows', '100000');
        $select->setRows(5);
        $select->setStart($page * 5);

        $results = $this->solarium->select($select);

        $data = $results->getData();
        $expanded = isset($data['expanded']) ? $data['expanded'] : [];

        /** @var \Solarium\QueryType\Select\Result\Document $document */
        foreach ($results as $document) {
            $item = $document->getFields();
            if (isset($expanded[$item['sector_s']])) {
                $item['_expanded_'] = $expanded[$item['sector_s']]['docs'];
            }
            $items[] = $item;
        }


        return $items;
    }
}
