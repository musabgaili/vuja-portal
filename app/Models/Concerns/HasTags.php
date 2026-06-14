<?php

namespace App\Models\Concerns;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

/** Polymorphic CRM tagging for any model (companies, contacts, opportunities). */
trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /** Sync tags from a comma-separated string, creating any new tags. */
    public function syncTagsFromString(?string $csv): void
    {
        $names = collect(explode(',', (string) $csv))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique();

        $ids = $names->map(fn ($name) => Tag::firstOrCreate(
            ['name' => $name],
            ['color' => '#'.substr(md5(Str::lower($name)), 0, 6)]
        )->id);

        $this->tags()->sync($ids->all());
    }

    public function tagList(): string
    {
        return $this->tags->pluck('name')->implode(', ');
    }
}
