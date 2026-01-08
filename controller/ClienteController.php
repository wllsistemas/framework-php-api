<?php

class ClienteController
{
    public function show(Request $request, Response $response)
    {
        return $response->json([
            'message' => 'Lista de clientes',
        ], 200);
    }

    public function findById(Request $request, Response $response)
    {
        return $response->json([
            'message' => 'Cliente encontrado',
        ], 200);
    }

    public function add(Request $request, Response $response)
    {
        return $response->json([
            'message' => 'Cliente cadastrado',
        ], 201);
    }

    public function update(Request $request, Response $response)
    {
        return $response->json([
            'message' => 'Cliente atualizado',
        ], 202);
    }

    public function delete(Request $request, Response $response)
    {
        return $response->json([
            'message' => 'Cliente deletado',
        ], 202);
    }
}
