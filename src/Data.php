<?php

namespace SolrGrouping;

use GuzzleHttp\Client;

class Data
{
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => 'https://www.donneesquebec.ca/recherche/fr/dataset/570ae2a1-3665-4196-9ee0-bb5417d5a08f/resource/e1f800f3-0357-4665-80cf-a0bf00dab740/download/',
        ]);
    }

    public function getOffers()
    {
        $sectors = [];
        $jobs = [];

        $response = $this->httpClient->get('offre-emploi.json');
        $data = \GuzzleHttp\json_decode($response->getBody()->getContents());

        foreach ($data as $offer) {
            if (!isset($sectors[$offer->Secteur_emploi])) {
                $id = sha1($offer->Secteur_emploi);
                $sectors[$offer->Secteur_emploi] = [
                    'type_s' => 'sector',
                    'name_s' => $offer->Secteur_emploi,
                    'statuses_ss' => [],
                    'organizations_ss' => [],
                    'id_s' => $id,
                    'sector_s' => $id,
                ];
            }
            $sectors[$offer->Secteur_emploi]['organizations'][] = $offer->Nom_organisation;
            $sectors[$offer->Secteur_emploi]['statuses'][] = $offer->Statut_emploi;
            $sectors[$offer->Secteur_emploi]['statuses'] = array_unique($sectors[$offer->Secteur_emploi]['statuses']);
            $sectors[$offer->Secteur_emploi]['organizations']
                = array_unique($sectors[$offer->Secteur_emploi]['organizations']);
            $jobs[] = [
                'type_s' => 'job',
                'id_s' => $offer->Emploi_ID,
                'start_dt' => $offer->Date_debut_postuler,
                'limit_dt' => $offer->Date_limite_postuler,
                'sector_s' => $sectors[$offer->Secteur_emploi]['id_s'],
                'sector_title_s' => $sectors[$offer->Secteur_emploi]['name_s'],
                'status_s' => $offer->Statut_emploi,
                'location_s' => $offer->Emplacement,
            ];
        }

        return [
            'sectors' => array_values($sectors),
            'jobs' => array_values($jobs),
        ];
    }
}
