<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Funcionario extends Model
{
    use HasFactory;

    protected $table = 'funcionarios';

    protected $fillable = [
        'nome',
        'salario',
        'email',
    ];
   //ajustes para o cast
    protected $casts = [
        'salario' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $rules = [
        'nome' => 'required|string|max:255',
        'salario' => 'required|numeric|min:0',
        'email' => 'required|email|unique:funcionarios,email',
    ];
}
