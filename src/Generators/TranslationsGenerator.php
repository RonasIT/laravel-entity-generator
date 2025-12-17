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
        $isTranslationFileExists = file_exists($this->translationPath);

        if (!$isTranslationFileExists && $this->isStubExists('validation')) {
            $this->createTranslate();

            return;
        }

        if ($isTranslationFileExists) {
            $this->setTranslationFileValue('exceptions.not_found', ':Entity does not exist');
        }
    }

    protected function createTranslate(): void
    {
        $stubPath = config('entity-generator.stubs.validation');

        $content = "<?php\n\n" . view($stubPath)->render();

        file_put_contents($this->translationPath, $content);

        $createMessage = "Created a new Translations dump on path: {$this->translationPath}";

        event(new SuccessCreateMessage($createMessage));
    }

    protected function setTranslations(): void
    {
        $config = ArrayFile::open($this->translationPath);

        $config->set($key, $value);

        $config->write();
    }
}
