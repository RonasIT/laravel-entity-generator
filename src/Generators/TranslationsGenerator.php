<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Events\SuccessCreateMessage;

class TranslationsGenerator extends EntityGenerator
{
    protected $translationPath;

    public function __construct()
    {
        parent::__construct();

        $this->translationPath = Arr::get($this->paths, 'translations', 'resources/lang/en/validation.php');
    }

    public function generate(): void
    {
        if (!file_exists($this->translationPath) && $this->isStubExists('validation')) {
            $this->createTranslate();
        }
        
        if ($this->isTranslationMissed('validation.exceptions.not_found') && $this->isStubExists('translation_not_found')) {
            $this->appendNotFoundException();
        }
    }

    protected function isTranslationMissed($translation) : bool
    {
        return __($translation) === 'validation.exceptions.not_found';
    }

    protected function createTranslate(): void
    {
        $stubPath = config('entity-generator.stubs.validation');

        $content = "<?php \n\n" . view($stubPath)->render();

        file_put_contents($this->translationPath, $content);

        $createMessage = "Created a new Translations dump on path: {$this->translationPath}";
        
        event(new SuccessCreateMessage($createMessage));
    }

    protected function appendNotFoundException(): void
    {
        $content = file_get_contents($this->translationPath);
        
        $stubPath = config('entity-generator.stubs.translation_not_found');
        
        $stubContent = view($stubPath)->render();

        $fixedContent = preg_replace('/\]\;\s*$/', "\n\t{$stubContent}", $content);
        
        file_put_contents($this->translationPath, $fixedContent);
    }
}