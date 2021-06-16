<?php

namespace Gsferro\TranslationSolutionEasy\Models;

class TranslationSolutionEasy extends \Spatie\TranslationLoader\LanguageLine
{
    protected $table = "language_lines";
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!empty(config('translationsolutioneasy.connection-sqlite'))) {
            $this->setConnection(config('translationsolutioneasy.connection-sqlite'));
        } elseif (config('translationsolutioneasy.connection') != null) {
            $this->setConnection(config('translationsolutioneasy.connection'));
        }
    }

    /*
    |---------------------------------------------------
    | Scopes
    |---------------------------------------------------
    */
    public function scopeKey($q, $key)
    {
        return $q->where('key', $key);
    }

    public function scopeGroup($q, $group = '*')
    {
        return $q->where('group', $group);
    }
}