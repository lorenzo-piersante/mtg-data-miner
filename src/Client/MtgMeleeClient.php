<?php

namespace App\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

class MtgMeleeClient
{
    private const BASE_URL = 'https://melee.gg/';

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getTournamentResults(
        int $lastRoundId,
        int $offset = 0,
        int $limit = 500
    ): array {
        if ($limit > 500) {
            throw new InvalidArgumentException('Max supported limit is 500 for melee.gg api');
        }

        $httpClient = new Client();

        $requestBody = [
            'draw' => 6,
            'columns' => [
                [
                    'data' => 'Rank',
                    'name' => 'Rank',
                    'searchable' => true,
                    'orderable' => true,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'Player',
                    'name' => 'Player',
                    'searchable' => false,
                    'orderable' => false,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'Decklists',
                    'name' => 'Decklists',
                    'searchable' => false,
                    'orderable' => false,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'MatchRecord',
                    'name' => 'MatchRecord',
                    'searchable' => false,
                    'orderable' => false,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'GameRecord',
                    'name' => 'GameRecord',
                    'searchable' => false,
                    'orderable' => false,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'Points',
                    'name' => 'Points',
                    'searchable' => true,
                    'orderable' => true,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'OpponentMatchWinPercentage',
                    'name' => 'OpponentMatchWinPercentage',
                    'searchable' => false,
                    'orderable' => true,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'TeamGameWinPercentage',
                    'name' => 'TeamGameWinPercentage',
                    'searchable' => false,
                    'orderable' => true,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'OpponentGameWinPercentage',
                    'name' => 'OpponentGameWinPercentage',
                    'searchable' => false,
                    'orderable' => true,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'FinalTiebreaker',
                    'name' => 'FinalTiebreaker',
                    'searchable' => false,
                    'orderable' => true,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                [
                    'data' => 'OpponentCount',
                    'name' => 'OpponentCount',
                    'searchable' => true,
                    'orderable' => true,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ]
            ],
            'order' => [
                [
                    'column' => 0,
                    'dir' => 'asc',
                ]
            ],
            'start' => $offset,
            'length' => $limit,
            'search' => [
                'value' => '',
                'regex' => false,
            ],
            'roundId' => $lastRoundId,
        ];

        $response = $httpClient->post(
            self::BASE_URL . 'Standing/GetRoundStandings',
            [
                'form_params' => $requestBody,
                'headers' => [
                    'Host' => 'melee.gg',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:132.0) Gecko/20100101 Firefox/132.0',
                    'Accept' => 'application/json; charset=UTF-8',
                    'Cookie' => getEnv('COOKIE'),
                ]
            ]
        );

        $body = $response->getBody()->getContents();

        $decodedResponseBody = json_decode($body);

        $deckLists = $decodedResponseBody->data;

        $output = [];
        foreach ($deckLists as $deckList) {
            $output[] = [
                'player' => $deckList->Team->Players[0]->DisplayName,
                'deckList' => $deckList->Decklists[0]->DecklistName,
                'matchRecord' => $deckList->MatchRecord
            ];
        }

        return $output;
    }
}
