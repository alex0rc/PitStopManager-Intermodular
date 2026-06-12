<?php

namespace Database\Seeders\Concerns;

use App\Models\Circuit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;

trait ResolvesSeedCircuits
{
    protected function seededCircuitSlugs(): array
    {
        return [
            'kartodromo-lucas-guerrero' => [
                'name'   => 'Kartódromo Internacional Lucas Guerrero',
                'needle' => 'Lucas Guerrero',
            ],
            'racing-center-gilesias' => [
                'name'   => 'Racing Center Gilesias',
                'needle' => 'Gilesias',
            ],
            'karting-alacant' => [
                'name'   => 'Karting Alacant',
                'needle' => 'Karting Alacant',
            ],
            'gokarts-mar-menor' => [
                'name'   => 'Go-Karts Mar Menor',
                'needle' => 'Mar Menor',
            ],
            'circuit-ricardo-tormo' => [
                'name'   => 'Circuit Ricardo Tormo',
                'needle' => 'Ricardo Tormo',
            ],
            'karting-horta-nord' => [
                'name'   => 'Karting Horta Nord',
                'needle' => 'Horta Nord',
            ],
            'karting-los-garres' => [
                'name'   => 'Karting Los Garres',
                'needle' => 'Los Garres',
            ],
            'karting-nucia-outdoor' => [
                'name'   => 'Karting La Nucía Outdoor',
                'needle' => 'Nuc',
            ],
            'circuito-zuera-zaragoza' => [
                'name'   => 'Circuito de Zuera',
                'needle' => 'Zuera',
            ],
            'dakart-indoor-burjassot' => [
                'name'   => 'Dakart Indoor',
                'needle' => 'Dakart',
            ],
        ];
    }

    protected function resolveSeededCircuit(string $slug): Circuit
    {
        if (!array_key_exists($slug, $this->seededCircuitSlugs())) {
            throw (new ModelNotFoundException)->setModel(Circuit::class, [$slug]);
        }

        $meta = $this->seededCircuitSlugs()[$slug];

        if ($this->circuitsHaveSlugColumn()) {
            $bySlug = Circuit::where('slug', $slug)->first();
            if ($bySlug) {
                return $bySlug;
            }
        }

        $byName = Circuit::where('name', $meta['name'])->first();
        if ($byName) {
            $this->backfillCircuitSlug($byName, $slug);

            return $byName;
        }

        $byNeedle = Circuit::where('name', 'like', '%'.$meta['needle'].'%')->first();
        if ($byNeedle) {
            $this->backfillCircuitSlug($byNeedle, $slug);

            return $byNeedle;
        }

        $byNormalized = $this->findCircuitByNormalizedNeedle($meta['needle']);
        if ($byNormalized) {
            $this->backfillCircuitSlug($byNormalized, $slug);

            return $byNormalized;
        }

        throw (new ModelNotFoundException(
            "Circuit [{$slug}] not found. Total circuits: ".Circuit::count().". Available: ".$this->availableCircuitNames()
        ))->setModel(Circuit::class, [$slug]);
    }

    protected function prepareSeededCircuits(): void
    {
        foreach (array_keys($this->seededCircuitSlugs()) as $slug) {
            if ($this->circuitsHaveSlugColumn() && Circuit::where('slug', $slug)->exists()) {
                continue;
            }

            $this->resolveSeededCircuit($slug);
        }
    }

    protected function upsertSeededCircuit(array $row, string $slug): Circuit
    {
        $payload = $this->circuitsHaveSlugColumn()
            ? array_merge($row, ['slug' => $slug])
            : $row;

        $circuit = null;

        if ($this->circuitsHaveSlugColumn()) {
            $circuit = Circuit::where('slug', $slug)->first();
        }

        if (!$circuit) {
            $circuit = Circuit::where('name', $row['name'])->first();
        }

        if (!$circuit && isset($this->seededCircuitSlugs()[$slug]['needle'])) {
            $circuit = Circuit::where('name', 'like', '%'.$this->seededCircuitSlugs()[$slug]['needle'].'%')->first();
        }

        if ($circuit) {
            $circuit->update($payload);

            return $circuit->fresh();
        }

        return Circuit::create($payload);
    }

    private function circuitsHaveSlugColumn(): bool
    {
        return Schema::hasColumn('circuits', 'slug');
    }

    private function backfillCircuitSlug(Circuit $circuit, string $slug): void
    {
        if ($this->circuitsHaveSlugColumn() && $circuit->slug !== $slug) {
            $circuit->update(['slug' => $slug]);
        }
    }

    private function findCircuitByNormalizedNeedle(string $needle): ?Circuit
    {
        $target = $this->normalize($needle);

        foreach (Circuit::all() as $circuit) {
            if (str_contains($this->normalize($circuit->name), $target)) {
                return $circuit;
            }
        }

        return null;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return preg_replace('/[^a-z0-9]+/', '', $ascii ?: $value) ?? '';
    }

    private function availableCircuitNames(): string
    {
        return Circuit::query()
            ->orderBy('id')
            ->get()
            ->map(function (Circuit $circuit) {
                if ($this->circuitsHaveSlugColumn() && $circuit->slug) {
                    return "{$circuit->slug} ({$circuit->name})";
                }

                return $circuit->name;
            })
            ->implode(', ');
    }
}
