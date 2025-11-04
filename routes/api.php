<?php
use App\Http\Controllers\FuncionarioController;
use Illuminate\Support\Facades\Route;

Route::get('funcionarios', [FuncionarioController::class, 'index']);
Route::post('funcionarios', [FuncionarioController::class, 'store']);
Route::put('funcionarios/{id}', [FuncionarioController::class, 'update']);
Route::delete('funcionarios/{id}', [FuncionarioController::class, 'destroy']);
//Route::get('/', function () {
//    return view('welcome');
//});
