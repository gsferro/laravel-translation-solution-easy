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

        return ($this->existTrans($key)
            ? __("{$value}")
            : $value
        );
    }

    /**
     * Verifica se a tabela foi configurada usando o nome da tabela e se a coluna invoca foi colocada para traduzir
     *
     * @param $key
     * @return bool
     */
    private function isConfigTable($key): bool
    {
        $table  = $this->getTable();
        $config = config('translationsolutioneasy.translate-tables');
        return (
            // tbl esta configurada
            array_key_exists($table, $config) && (
                // verifica o campo tanto único, como string, quanto em array
                is_array($config[ $table ])
                    ? in_array($key, $config[$table])
                    : $config[$table] == $key
            )
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
        return $this->existsConfig() && (
                $this->isConfigTable($key) || $this->isSetInModel($key)
            );
    }

    /**
     * verifica se existe configração
     * @return bool
     */
    private function existsConfig(): bool
    {
        return !empty(config('translationsolutioneasy.translate-tables'));
    }
}