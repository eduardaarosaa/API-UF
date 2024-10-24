<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class MunicipioIntegrationTest extends TestCase
{
    public function testListMunicipiosReturnsDataByUf()
    {
        // Simula a resposta da API para a UF 'SP'
        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/SP' => Http::sequence()
                ->push([
                    'data' => [
                        ['id' => 3500105, 'nome' => 'Adamantina'],
                        ['id' => 3500204, 'nome' => 'Adolfo'],
                        ['id' => 3500303, 'nome' => 'Aguaí'],
                        ['id' => 3500402, 'nome' => 'Águas da Prata'],
                        ['id' => 3500501, 'nome' => 'Águas de Lindóia'],
                        ['id' => 3500550, 'nome' => 'Águas de Santa Bárbara'],
                        ['id' => 3500600, 'nome' => 'Águas de São Pedro'],
                        ['id' => 3500709, 'nome' => 'Agudos'],
                        ['id' => 3500758, 'nome' => 'Alambari'],
                        ['id' => 3500808, 'nome' => 'Alfredo Marcondes'],
                    ],
                    'meta' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 10,
                        'total' => 10,
                    ],
                ]),
        ]);

        // Faça a requisição para a rota que lista os municípios, passando a UF 'SP'
        $response = $this->get('/api/municipios/SP');

        // Verifique se a resposta tem o status 200
        $response->assertStatus(200);

        // Verifique se o número de municípios retornados está correto
        $this->assertCount(10, $response->json('data'));

        // Verifique se os dados dos municípios que você espera estão corretos
        $response->assertJsonFragment([
            'nome' => 'Adamantina',
            'id' => 3500105, // Aqui agora estamos usando um ID numérico
        ]);

        $response->assertJsonFragment([
            'nome' => 'Adolfo',
            'id' => 3500204, // Aqui também
        ]);
    }
}
