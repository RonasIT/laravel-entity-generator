<?php

namespace RonasIT\Support\Generators;
 
use RonasIT\Support\Events\SuccessCreateMessage;

class TranslationsGenerator extends EntityGenerator
{
    protected $translationPath;
    protected $dumpPath;
    protected $notFoundExceptionDumpPath;

    public function __construct()
    {
        parent::__construct();

        $this->translationPath = $this->paths['translations'];
        $this->dumpPath = stubs_path("dumps/validation.blade.php");
        $this->notFoundExceptionDumpPath = stubs_path("dumps/translation_not_found.blade.php");
    }

    public function generate()
    {
        if (!file_exists($this->translationPath)) {
            $this->createTranslate();
        }

        if(__('validation.exceptions.not_found') !== 'validation.exceptions.not_found') {
            $this->appendNotFoundException();
        }
    }

    protected function createTranslate()
    {
        file_put_contents($this->translationPath, file_get_contents($this->dumpPath));

        $createMessage = "Created a new Translations dump on path: {$this->translationPath}";
        
        event(new SuccessCreateMessage($createMessage));
    }

    protected function appendNotFoundException()
    {
        $content = file_get_contents($this->translationPath);
        
        $str = file_get_contents($this->notFoundExceptionDumpPath);
        
        $fixedContent = preg_replace('/\]\;\s*$/', "\n    {$str}", $content);
        
        file_put_contents($this->translationPath, $fixedContent);
    }
}