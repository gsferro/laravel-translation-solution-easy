<?php

namespace Gsferro\TranslationSolutionEasy\Console\Commands;

use Exception;
use Gsferro\TranslationSolutionEasy\Models\TranslationSolutionEasy;
use Gsferro\TranslationSolutionEasy\Services\ReversoTranslation;
use Illuminate\Config\Repository;
use Illuminate\Console\Command;

class TranslationFoldersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:translate-folders {--folder= : resources/langs/locale/<your-folder>} {--file= : file name}';
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

        /*
        |---------------------------------------------------
        | validation
        |---------------------------------------------------
        */

        if (!is_dir($this->pathBaseLocale)) {
            return $this->comment('Sorry, dont the folder language config in your application.');
        }

        if (count($this->langsSupport) == 0) {
            $this->line("");
            return $this->comment('Sorry, not language config in your application.');
        }

        if (count($this->langsSupport) == 1 && in_array($this->locale, $this->langsSupport)) {
            $this->info('Attention! The configured language is already in your application.');
            if ($this->confirm('Abortion the translation for you set up?!')) {
                return $this->comment('Okay. Abort this!');
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*
        |---------------------------------------------------
        | Caso passe algum paramentro
        |---------------------------------------------------
        */
        try {
            $folders = $this->optionFolder();
            $files   = $this->optionFile($folders);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        // exibindo linguas
        $this->comment("Language from locale app: [ {$this->locale} ]");
        $this->comment("Languages that will be translated together: [ " . implode(" | ", $this->langsSupport) . " ]");

        if (!$this->confirm('Translate to all languages?', true)) {
            $langsSupport       = $this->choice("Translate into what languages?!", $this->langsSupport, null, null, true);
            $this->langsSupport = (is_array($langsSupport) ? $langsSupport : [$langsSupport]);
        }

        // executando tradução
        return $this->exec($folders, $files);
    }

    /**
     * @param array $folders
     * @param array $files
     */
    private function exec(array $folders, array $files)
    {
        $this->line("");
        $this->line("Total of Languages:");
        $langsBar = $this->output->createProgressBar(count($this->langsSupport));
        $langsBar->start();

        try {
            foreach ($this->langsSupport as $lang) {
                $this->execInFolders($folders, $files, $lang);
                $langsBar->advance();
            }
            $langsBar->finish();

            $this->line("");
            $this->comment('Thanks for using me!');
            $this->comment("\7");
        } catch (Exception $e) {
            $this->error("Oops... {$e->getMessage()}");
        }
    }

    /**
     * @param $original
     * @param $lang
     * @param $group
     * @throws Exception
     */
    private function translate($original, $lang, $group)
    {
        // busca os dados
        $rows = json_decode(file_get_contents($original), true);

        $this->line("");
        $this->line("");
        $this->line("Total registres per file [ {$rows} ] ");
        $col = $this->output->createProgressBar(count($rows));

        $translation = new ReversoTranslation($this->locale, $lang);

        foreach ($rows as $key => $line) {

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
                    'text' => array_merge($text ?? [], $trans[ "translate" ]),
                ]);
            }
            $col->advance();
        }

        $col->finish();
        $this->line("");
    }

    /**
     * @return array|false
     */
    private function getFolders()
    {
        return $paths = scandir($this->pathBaseLocale, 1);
    }

    /**
     * @param $path
     * @return array|false
     */
    private function getFiles($path)
    {
        return $folders = scandir("{$this->pathBaseLocale}/{$path}", 1);
    }

    /**
     * @param array $folders
     * @param array $files
     * @param $lang
     * @throws Exception
     */
    private function execInFolders(array $folders, array $files, $lang)
    {
        $this->comment("Total of folders:");
        $foldersBar = $this->output->createProgressBar(count($folders));
        $foldersBar->start();
        foreach ($folders as $folder) {
            $this->execInFiles($files, $lang, $folder);
            $foldersBar->advance();
        }
        $foldersBar->finish();
    }

    /**
     * @param array $files
     * @param $lang
     * @param $folder
     * @throws Exception
     */
    private function execInFiles(
        array $files,
        $lang,
        $folder
    ) {
        $this->comment("Total of files:");
        $filesBar = $this->output->createProgressBar(count($files));
        $filesBar->start();
        foreach ($files as $file) {
            $this->translate("{$this->pathBaseLocale}/{$folder}/{$file}", $lang, "{$folder}.{$file}");
            $filesBar->advance();
        }
        $filesBar->finish();
    }

    /**
     * @return array|bool|string|null
     * @throws Exception
     */
    private function optionFolder()
    {
        $folders = $this->option('folder') ??
            $this->choice("What is the translation folder?!", $this->getFolders(), null, null, true);// validação
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                $this->folderNotExists($folder);
            }
        } else {
            $this->folderNotExists($folders);
        }
        return $folders;
    }

    /**
     * @param $folders
     * @return array|bool|string|null
     * @throws Exception
     */
    private function optionFile($folders)
    {
        $files = $this->option('file') ??
            $this->choice("What is the translation file?!", $this->getFiles($folders), null, null,
                true);// validação
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->fileNotExists($folders, $file);
            }
        } else {
            $this->fileNotExists($folders, $files);
        }
        return $files;
    }

    /**
     * @param $folders
     * @param $file
     * @throws Exception
     */
    private function fileNotExists($folders, $file)
    {
        if (!file_exists("{$this->pathBaseLocale}/$folders}/{$file}")) {
            throw new Exception("Sorry, file [ {$file} ] in folder [ {$folders} ] dont exists.");
        }
    }

    /**
     * @param $folder
     * @throws Exception
     */
    private function folderNotExists($folder)
    {
        if (!is_dir("{$this->pathBaseLocale}/$folder}")) {
            throw new Exception("Sorry, folder [ {$folder} ] dont exists.");
        }
    }
}
