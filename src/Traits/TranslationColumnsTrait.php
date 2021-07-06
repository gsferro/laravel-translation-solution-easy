<?php

namespace Gsferro\TranslationSolutionEasy\Traits;

trait TranslationColumnsTrait
{
    public function getTranslationColumns(): array
    {
        return $this->translationColumns ?? [];
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->existTrans($key)) {
            return __("{$value}");
        }

        return $value;
    }

    /**
     * Caso tenha sido configurado dentro de translationsolutioneasy com o nome da tabela e a coluna
     *
     * @param $key
     * @return bool
     */
    private function isConfigTable($key): bool
    {
        $table = $this->getTable();
        return (
            !empty(config('translationsolutioneasy.translate-tables')) &&
            array_key_exists($table, config('translationsolutioneasy.translate-tables')) &&
            in_array($key, config('translationsolutioneasy.translate-tables')[$table])
        );
    }

    /**
     * Caso tenha sido feito o translate inline e setado na model
     *
     * @param $key
     * @return bool
     */
    private function isSetInModel($key): bool
    {
        $columns = $this->getTranslationColumns();
        return !empty($columns) && in_array($key, $columns);
    }

    /**
     * Verifica primeiro em config e dps na model se alguma é verdadeira
     *
     * @param $key
     * @return bool
     */
    private function existTrans($key): bool
    {
        return $this->isConfigTable($key) || $this->isSetInModel($key);
    }
}