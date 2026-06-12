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

        $available = Circuit::query()
            ->select(['id', 'name', 'slug'])
            ->orderBy('id')
            ->get()
            ->map(fn (Circuit $circuit) => $circuit->slug
                ? "{$circuit->slug} ({$circuit->name})"
                : $circuit->name)
            ->implode(', ');

        throw new ModelNotFoundException(
            "Circuit [{$slug}] not found. Total circuits: ".Circuit::count().". Available: {$available}"
        );
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
}
