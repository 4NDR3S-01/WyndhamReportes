<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EntidadCertificado extends Model
{
    protected $table = 'entidades_certificado';
    protected $fillable = ['nombre', 'activo'];
    protected function casts(): array { return ['activo' => 'boolean']; }
}
