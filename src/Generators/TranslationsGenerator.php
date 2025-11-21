<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Events\SuccessCreateMessage;
use Winter\LaravelConfigWriter\ArrayFile;

class TranslationsGenerator extends EntityGenerator
{
    protected $translationPath;

    public function __construct()
    {
        parent::__construct();

        $this->translationPath = base_path(Arr::get($this->paths, 'translations', 'resources/lang/en/validation.php'));
    }

    public function generate(): void
    {
        if (!file_exists($this->translationPath) && $this->isStubExists('validation')) {
            $this->createTranslate();

            return;
        }

        $isExceptionsMissed = $this->isExceptionsMissed('validation.exceptions');

        $config = ArrayFile::open($this->translationPath);

        $config->set('exceptions.not_found', ':Entity does not exist');

        $config->write();

        if ($isExceptionsMissed && $this->isStubExists('validation_exceptions_comment')) {
            $this->appendExceptionComment();
        }
    }

    protected function isExceptionsMissed($translation) : bool
    {
        return __($translation) === 'validation.exceptions';
    }

    protected function createTranslate(): void
    {
        $stubPath = config('entity-generator.stubs.validation');

        $content = "<?php\n\n" . view($stubPath)->render();

        file_put_contents($this->translationPath, $content);

        $createMessage = "Created a new Translations dump on path: {$this->translationPath}";

        event(new SuccessCreateMessage($createMessage));
    }

    protected function appendExceptionComment(): void
    {
        $content = file_get_contents($this->translationPath);

        $stubPath = config('entity-generator.stubs.validation_exceptions_comment');

        $stubContent = view($stubPath)->render();

        $fixedContent = preg_replace("/(\s*)('exceptions'\s*=>)/", "\n    {$stubContent}$0", $content);

        file_put_contents($this->translationPath, $fixedContent);
    }
}
