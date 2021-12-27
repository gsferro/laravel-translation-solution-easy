<?php

namespace Gsferro\TranslationSolutionEasy\Console\Commands;

use Exception;
use Gsferro\TranslationSolutionEasy\Interfaces\TranslationCommandInterface;
use Gsferro\TranslationSolutionEasy\Traits\TranslationCommandTrait;
use Illuminate\Console\Command;

class TranslationFilesCommand extends Command implements TranslationCommandInterface
{
    use TranslationCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:translate-files {--file= : File name} {--lang= : Language} {--force}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate the values contained within the folders!';

    /**
     * @var string
     */
    private $pathBase;
    /**
     * @var string
     */
    private $pathBaseLocale;
    /**
     * @var array
     */
    private $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setLangs();
        $this->pathBase       = resource_path("/lang");
        $this->pathBaseLocale = "{$this->pathBase}/{$this->locale}";
        $this->messageFinish  = "gsferro:translate-tables";
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
            // validation generic
            $this->validation();

            /*
            |---------------------------------------------------
            | get options files
            |---------------------------------------------------
            */
            $this->optionFile();

            /*
            |---------------------------------------------------
            | executando tradução
            |---------------------------------------------------
            */
            $this->exec();
        } catch (Exception $e) {
            return $this->comment($e->getMessage());
        }
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
    public function execInCommand($lang)
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
        $this->comment("Finish files in lang [ {$lang} ]");
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
        $this->comment("Total registres per file [ {$file} ] in lang [ {$lang} ]:");
        $max = count($rows, COUNT_RECURSIVE);
        $col = $this->output->createProgressBar($max);

        $group = ($file == $this->locale ? "*" : "{$file}");
        foreach ($rows as $key => $line) {
            $col->advance();
            if (is_array($line)) {
                $group .= ".{$key}";
                $this->lineIsArray($lang, $line, $group, $col);
                continue;

            }
            $this->persist($lang, $key, $line, $group);
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
     * @param array $line
     * @param $group
     */
    private function lineIsArray($lang, array $line, $group, $col): void
    {
        foreach ($line as $key => $ln) {
            $col->advance();
            if (is_array($ln)) {
                $group .= ".{$key}";
                $this->lineIsArray($lang, $ln, $group, $col);
                continue;
            }

            $this->persist($lang, $key, $ln, $group);
        }
    }
}
