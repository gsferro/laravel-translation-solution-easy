<?php

namespace Gsferro\TranslationSolutionEasy\Console\Commands;

use Exception;
use Gsferro\TranslationSolutionEasy\Models\TranslationSolutionEasy;
use Gsferro\TranslationSolutionEasy\Services\ReversoTranslation;
use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TranslationFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:translate-files {--file= : file name} {--lang= : Language}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate the values contained within the folders!';
    /** * @var Repository */
    private $langsSupport;
    /** * @var Repository */
    private $locale;
    /**
     * @var string
     */
    private $pathBase;
    /**
     * @var string
     */
    private $pathBaseLocale;
    /** * @var array */
    private $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->locale         = config('app.locale');
        $this->langsSupport   = array_keys(config('laravellocalization.supportedLocales'));
        $this->pathBase       = resource_path("/lang");
        $this->pathBaseLocale = "{$this->pathBase}/{$this->locale}";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            /*
            |---------------------------------------------------
            | validation
            |---------------------------------------------------
            */
            if (!is_dir($this->pathBaseLocale)) {
                throw new Exception("Sorry, dont the folder language [ {$this->locale} ] config in your application.");
            }

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

            // get options files
            $this->optionFile();
        } catch (Exception $e) {
            return $this->comment($e->getMessage());
        }

        // exibindo linguas
        $this->comment("Language from locale app: [ {$this->locale} ]");
        $this->comment("Languages that will be translated together: [ " . implode(" | ", $this->langsSupport) . " ]");

        if ((
                count($this->langsSupport) > 1) &&
            (!$lang) &&
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

        // executando tradução
        return $this->exec();
    }

    private function exec()
    {
        $this->line("");
        $this->line("Total of Languages:");
        $langsBar = $this->output->createProgressBar(count($this->langsSupport));
        $langsBar->start();

        try {
            DB::beginTransaction();
            foreach ($this->langsSupport as $lang) {
                $this->execInFiles($lang);
                $langsBar->advance();
            }
            DB::commit();
            $langsBar->finish();
            $this->line("");
            $this->comment("Finish Languages");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->comment("Oops... {$e->getMessage()}");
        }

        $this->line("");
        $this->comment('Thanks for using me!');
        $this->comment("\7");
    }

    /**
     * @return array|false
     */
    private function getFiles($incluirTodos = true)
    {
        $baseLocale = array_diff(scandir($this->pathBaseLocale, 1), ['..', '.']);
        $base       = file_exists("{$this->pathBase}/{$this->locale}.json") ? ["{$this->locale}.json"] : [];
        $todos      = $incluirTodos ? ["Todos"] : [];

        return array_merge($todos, $base, $baseLocale);
    }

    /**
     * @param $lang
     * @throws Exception
     */
    private function execInFiles($lang)
    {
        $this->line('');
        $this->line('');
        $this->comment("Total of files in lang [ {$lang} ]:");
        $filesBar = $this->output->createProgressBar(count($this->files));
        $filesBar->start();

        foreach ($this->files as $file) {
            $original = (str_contains($file, ".json")
                ? "{$this->pathBase}/{$file}"
                : "{$this->pathBaseLocale}/{$file}");

            $fileName = current(explode('.', $file));
            $this->translate("{$original}", $lang, $fileName);

            $filesBar->advance();
        }

        $filesBar->finish();
        $this->line("");
        $this->comment("Finish files the lang [ {$lang} ]");
    }

    /**
     * @param $original
     * @param $lang
     * @param $file
     * @throws Exception
     */
    private function translate($original, $lang, $file)
    {
        // busca os dados
        $rows = (($file == $this->locale)
            ? json_decode(file_get_contents($original), true)
            : require $original);

        $this->line("");
        $this->line("");
        $this->comment("Total registres per file [ {$file} ] in lang[ {$lang} ]:");
        $max = count($rows, COUNT_RECURSIVE);
        $col = $this->output->createProgressBar($max);

        // inicia service de tradução
        $translation = new ReversoTranslation($this->locale, $lang);

        $group = ($file == $this->locale ? "*" : "{$file}");
        foreach ($rows as $key => $line) {
            $col->advance();
            if (is_array($line)) {
                $group .= ".{$key}";
                $this->lineIsArray($lang, $line, $translation, $group, $col);
                continue;

            }
            $this->persist($lang, $key, $translation, $line, $group);
        }

        $col->finish();
        $this->line("");
        $this->comment("Finish file [ {$file} ] in lang [ {$lang} ]");
    }

    /**
     * @return array|bool|string|null
     * @throws Exception
     */
    private function optionFile()
    {
        $files = $this->option('file') ??
            $this->choice("What is the translation file",
                $this->getFiles(),
                0,
                count($this->getFiles()),
                true
            );

        // validação
        if (is_array($files)) {
            // pegando todos os arquivos
            if (current($files) == "Todos") {
                $files = $this->getFiles(false);
            }

            foreach ($files as $file) {
                $this->fileNotExists($file);
            }
        } else {
            $this->fileNotExists($files);
            $files = [$files];
        }

        $this->files = $files;
    }

    /**
     * @param $file
     * @throws Exception
     */
    private function fileNotExists($file)
    {
        if (!file_exists("{$this->pathBaseLocale}/{$file}") && !file_exists("{$this->pathBase}/{$file}")) {
            throw new Exception("Sorry, file [ {$file} ] dont exists.");
        }
    }

    /**
     * @param $lang
     * @param $key
     * @param ReversoTranslation $translation
     * @param $line
     */
    private function persist($lang, $key, ReversoTranslation $translation, $line, $group): void
    {
        if (str_contains($group, "*")) {
            $group = "*";
        }

        $trans = $translation->trans($line);
        if ($trans[ "success" ]) {
            $model = TranslationSolutionEasy::key($key)->group($group);
            if ($model->exists()) {
                $text = $model->first()->text;
            }
            TranslationSolutionEasy::updateOrCreate([
                'group' => $group,
                'key'   => $key,
            ], [
                'text' => array_merge($text ?? [], [$lang => $trans[ "translate" ]]),
            ]);
        }
    }

    /**
     * @param $lang
     * @param array $line
     * @param ReversoTranslation $translation
     * @param $group
     */
    private function lineIsArray($lang, array $line, ReversoTranslation $translation, $group, $col): void
    {
        foreach ($line as $key => $ln) {
            $col->advance();
            if (is_array($ln)) {
                $group .= ".{$key}";
                $this->lineIsArray($lang, $ln, $translation, $group, $col);
                continue;
            }

            $this->persist($lang, $key, $translation, $ln, $group);
        }
    }
}
