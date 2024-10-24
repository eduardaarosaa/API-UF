<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class MunicipioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configura o provider
        putenv('MUNICIPIO_PROVIDER=brasil_api');

        // Desativa a verificação SSL globalmente
        Http::preventStrayRequests(); // Impede que requisições não fake sejam feitas

        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/*' => Http::sequence()
                ->push(['nome' => 'Municipio A', 'codigo_ibge' => '123456'])
                ->push(['nome' => 'Municipio B', 'codigo_ibge' => '789012']),
            'https://servicodados.ibge.gov.br/api/v1/localidades/estados/*/municipios' => Http::sequence()
                ->push(['nome' => 'Municipio C', 'id' => '345678'])
                ->push(['nome' => 'Municipio D', 'id' => '901234']),
        ]);
    }
    public function testApiReturnsError()
    {
        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/*' => Http::sequence()
                ->push(500), // Simula um erro 500
        ]);

        $response = $this->get('/api/municipios/123456');

        $response->assertStatus(500);
    }

    public function testApiReturnsErrorWhenListingMunicipios()
    {
        // Simula um erro 404 para a API de municípios
        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/*' => Http::sequence()
                ->push(404),
        ]);

        $response = $this->get('/api/municipioss');
        
        $response->assertStatus(404);
    }
}
