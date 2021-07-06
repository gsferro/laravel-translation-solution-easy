<?php
namespace Gsferro\TranslationSolutionEasy\Traits;

use Exception;
use Gsferro\TranslationSolutionEasy\Models\TranslationSolutionEasy;
use Gsferro\TranslationSolutionEasy\Services\ReversoTranslation;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\DB;

trait TranslationCommandTrait
{
    /** * @var ReversoTranslation */
    protected $translation;
    /** * @var Repository */
    private $langsSupport;
    /** * @var Repository */
    private $locale;
    /** * @var string */
    private $messageFinish;

    private function setLangs()
    {
        $this->locale         = config('app.locale');
        $this->langsSupport   = array_keys(config('laravellocalization.supportedLocales'));
    }

    /**
     * @throws Exception
     */
    private function validation()
    {
        // remover lang locale
        $this->langsSupport = array_diff($this->langsSupport, [$this->locale]);

        // get options lang
        $lang = $this->option('lang');
        if (!empty($lang) && in_array($lang, array_keys(config('laravellocalization.supportedLocales')))) {
            $this->langsSupport = [$lang];
        }

        if (count($this->langsSupport) == 0) {
            throw new Exception('Sorry, not language config in your application.');
        }

        if (count($this->langsSupport) == 1 && in_array($this->locale, $this->langsSupport)) {
            throw new Exception('Attention! The configured language is already in your application');
        }
    }

    /**
     * Persist in model TranslationSolutionEasy
     *
     * @param string $lang
     * @param string $key
     * @param string $translate
     * @param string $group
     */
    private function persist(string $lang, string $key, string $translate, string $group = "*"): void
    {
        if (str_contains($group, "*")) {
            $group = "*";
        }

        $trans = $this->translation->trans($translate);
        if ($trans[ "success" ]) {
            $text = $this->textExists($key, $group);

            TranslationSolutionEasy::updateOrCreate([
                'group' => $group,
                'key'   => $key,
            ], [
                'text' => array_merge($text, [$lang => $trans[ "translate" ]]),
            ]);
        }
    }

    /**
     * Get in Model if Exisits
     *
     * @param string $key
     * @param string $group
     * @return array
     */
    private function textExists(string $key, string $group): array
    {
        $model = TranslationSolutionEasy::key($key)->group($group);
        if (!$model->exists()) {
            return [];
        }

        return $model->first()->text;
    }

    /**
     * execute execInCommand($lang)
     */
    public function exec()
    {
        $this->showLangs();

        $this->line("");
        $this->line("Total of Languages:");
        $langsBar = $this->output->createProgressBar(count($this->langsSupport));
        $langsBar->start();
        $this->line("");

        try {
            DB::beginTransaction();
            foreach ($this->langsSupport as $lang) {
                // inicia service de tradução
                $this->translation = new ReversoTranslation($this->locale, $lang);

                $this->execInCommand($lang);

                $langsBar->advance();
            }
            DB::commit();

            $langsBar->finish();
            $this->line("");
            $this->comment("Finish Languages");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Oops... {$e->getMessage()}");
        }

        $this->messageFinish();
    }

    private function showLangs()
    {
        // exibindo linguas
        $this->comment("Language from locale app: [ {$this->locale} ]");
        $this->comment("Languages that will be translated together: [ " . implode(" | ", $this->langsSupport) . " ]");

        if (
            (count($this->langsSupport) > 1) &&
            // (!$lang) &&
            (!$this->confirm('Translate to all languages?', true))
        ) {
            $langsSupport       = $this->choice("Translate into what languages",
                $this->langsSupport,
                null,
                count($this->langsSupport),
                true
            );
            $this->langsSupport = (is_array($langsSupport) ? $langsSupport : [$langsSupport]);
        }
    }

    private function messageFinish()
    {
        /*
        |---------------------------------------------------
        | Clear cache
        |---------------------------------------------------
        */
        $this->line("");
        $this->call('cache:clear');
        $this->line("");

        /*
        |---------------------------------------------------
        | Agradecimentos e sugestão de outro
        |---------------------------------------------------
        */
        if (!is_null($this->messageFinish)) {
            $this->comment('Then execute the command:');
            $this->line('');
            $this->comment("php artisan {$this->messageFinish}");
            $this->line("");
        }

        $this->comment('Thanks for using me!');
        $this->comment("\7");
    }
}