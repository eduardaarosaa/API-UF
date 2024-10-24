<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="API - UF munícipios", version="0.1")
 */
class MunicipioController extends Controller
{
  /**
    * @OA\GET(
    *     path="/api/municipios/{uf}",
    *     summary="Obter todos os municípios por estado",
    *     description="Recupera uma lista de municípios no estado especificado (UF).",
    *     tags={"Municípios"},
    *     @OA\Parameter(
    *         name="uf",
    *         in="path",
    *         description="O UF (código do estado) para o qual recuperar os municípios.",
    *         required=true,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="name",
    *         in="query",
    *         description="Filtrar pelo nome do município.",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="email",
    *         in="query",
    *         description="Filtrar por email (se aplicável).",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="page",
    *         in="query",
    *         description="Número da página para paginação.",
    *         required=false,
    *         @OA\Schema(type="integer")
    *     ),
    *     @OA\Parameter(
    *         name="limit",
    *         in="query",
    *         description="Número de resultados por página.",
    *         required=false,
    *         @OA\Schema(type="integer", default=10)
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Uma lista de municípios",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 property="data",
    *                 type="array",
    *                 @OA\Items(
    *                     type="object",
    *                     @OA\Property(property="nome", type="string"),
    *                     @OA\Property(property="id", type="integer"),
    *                     @OA\Property(property="ibge_code", type="integer", nullable=true)
    *                 )
    *             ),
    *             @OA\Property(
    *                 property="meta",
    *                 type="object",
    *                 @OA\Property(property="current_page", type="integer"),
    *                 @OA\Property(property="total", type="integer"),
    *                 @OA\Property(property="last_page", type="integer"),
    *                 @OA\Property(property="per_page", type="integer"),
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Requisição Inválida",
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Erro Interno do Servidor",
    *     )
    * )
    */
    public function index($uf, Request $request)
    {
        $provider = env('MUNICIPIO_PROVIDER');

        // dd($provider);
        switch ($provider) {
            case 'brasil_api':
                $response = Http::withOptions(['verify' => false])
                    ->get("https://brasilapi.com.br/api/ibge/municipios/v1/{$uf}");
                break;

            case 'ibge':
                $response = Http::withOptions(['verify' => false])
                    ->get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$uf}/municipios");
                break;

            default:
                return response()->json(['error' => 'Provider not found'], 400);
        }

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch data'], $response->status());
        }


        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);



        if ($provider == 'ibge') {
            $municipios = collect($response->json())->map(function ($municipio) {
                return [
                    'nome' => $municipio['nome'],
                    'id' => $municipio['id'],
                ];
            });
        } else {
            $municipios = collect($response->json())->map(function ($municipio) {
                return [
                    'nome' => $municipio['nome'],
                    'ibge_code' => $municipio['codigo_ibge'],
                ];
            });
        }


        $total = $municipios->count();
        $municipiosPaginated = $municipios->slice(($page - 1) * $limit, $limit);

        return response()->json([
            'data' => $municipiosPaginated,
            'meta' => [
                'current_page' => $page,
                'total' => $total,
                'last_page' => ceil($total / $limit),
                'per_page' => $limit,
            ],
        ]);
    }
}
