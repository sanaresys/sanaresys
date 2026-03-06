<?php

namespace App\Services;

use App\Models\Centros_Medico;
use Illuminate\Validation\ValidationException;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Str;

class TenantIdentityService
{
    public function generateSlug(string $nombreCentro): string
    {
        $slug = Str::slug($nombreCentro);

        if ($slug === '') {
            throw ValidationException::withMessages([
                'nombre_centro' => 'No se pudo generar un slug válido para el centro.',
            ]);
        }

        if (strlen($slug) > 63) {
            $slug = rtrim(substr($slug, 0, 63), '-');
        }

        return $slug;
    }

    public function validateSlugAvailable(string $slug, ?int $ignoreCentroId = null): void
    {
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw ValidationException::withMessages([
                'slug' => 'El slug solo puede usar minúsculas, números y guiones.',
            ]);
        }

        if (in_array($slug, $this->reservedSlugs(), true)) {
            throw ValidationException::withMessages([
                'slug' => 'El slug ingresado está reservado y no puede usarse.',
            ]);
        }

        $slugExists = Centros_Medico::on('mysql')
            ->where('slug', $slug)
            ->when($ignoreCentroId, fn ($query) => $query->where('id', '!=', $ignoreCentroId))
            ->exists();

        if ($slugExists) {
            throw ValidationException::withMessages([
                'slug' => 'Ya existe un centro con este slug.',
            ]);
        }

        $domain = $this->buildPrimaryDomain($slug);
        $domainExists = Domain::query()->where('domain', $domain)->exists();

        if ($domainExists) {
            throw ValidationException::withMessages([
                'slug' => 'El subdominio ya está ocupado.',
            ]);
        }
    }

    public function buildPrimaryDomain(string $slug): string
    {
        return strtolower("{$slug}.{$this->baseDomain()}");
    }

    public function baseDomain(): string
    {
        return (string) config('tenancy.base_domain', 'sanaresys.com');
    }

    protected function reservedSlugs(): array
    {
        return [
            'www',
            'admin',
            'api',
            'app',
            'mail',
            'ftp',
            'smtp',
            'sanaresys',
        ];
    }
}

