<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'trade_name',
        'cnpj',
        'inscricao_estadual',
        'address',
        'city',
        'state',
        'phone',
        'email',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function setCnpjAttribute(?string $value): void
    {
        $this->attributes['cnpj'] = $this->digitsOnly($value);
    }

    public function setInscricaoEstadualAttribute(?string $value): void
    {
        $this->attributes['inscricao_estadual'] = $this->alnumUpper($value);
    }

    private function digitsOnly(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', trim($value)) ?? '';

        return $normalized !== '' ? $normalized : null;
    }

    private function alnumUpper(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^A-Za-z0-9]+/', '', strtoupper(trim($value))) ?? '';

        return $normalized !== '' ? $normalized : null;
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(CompanyBankAccount::class);
    }

    public function paymentBatches(): HasMany
    {
        return $this->hasMany(PaymentBatch::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
