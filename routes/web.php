<?php
use App\Http\Controllers\FuncionarioController;
use Illuminate\Support\Facades\Route;
Route::get('funcionarios', [FuncionarioController::class, 'index']);
Route::post('funcionarios', [FuncionarioController::class, 'store']);
//Route::get('/', function () {
//Route::get('/', function () {
//    return view('welcome');
//});
