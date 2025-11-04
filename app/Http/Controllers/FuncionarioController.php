<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FuncionarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index(): JsonResponse
    {
        try {
            $funcionarios = Funcionario::select('id', 'nome', 'salario', 'email', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $funcionarios,
                'count' => $funcionarios->count()
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar funcionários',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'salario' => 'required|numeric|min:0|max:9999999.99',
                'email' => 'required|email|unique:funcionarios,email'
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'salario.required' => 'O campo salário é obrigatório.',
                'salario.numeric' => 'O salário deve ser um valor numérico.',
                'salario.min' => 'O salário não pode ser negativo.',
                'email.required' => 'O campo email é obrigatório.',
                'email.email' => 'Informe um email válido.',
                'email.unique' => 'Este email já está cadastrado.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $funcionario = Funcionario::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Funcionário criado com sucesso',
                'data' => $funcionario
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar funcionário',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $funcionario = Funcionario::find($id);
            if(!$funcionario){
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nome' => 'sometimes|required|string|max:255',
                'salario' => 'sometimes|required|numeric|min:0|max:9999999.99',
                'email' => 'sometimes|required|email|unique:funcionarios,email,'.$id
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'salario.required' => 'O campo salário é obrigatório.',
                'salario.numeric' => 'O salário deve ser um valor numérico.',
                'salario.min' => 'O salário não pode ser negativo.',
                'email.required' => 'O campo email é obrigatório.',
                'email.email' => 'Informe um email válido.',
                'email.unique' => 'Este email já está cadastrado.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $funcionario->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Funcionário atualizado com sucesso',
                'data' => $funcionario
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar funcionário',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $funcionario = Funcionario::find($id);
            if(!$funcionario){
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            $funcionario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Funcionário deletado com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar funcionário',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }

    }
}
