<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use PhpParser\Node\Scalar\String_;
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
            $this->setTranslations();
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

        $ast = $config->getAst();

        $configKeys = collect($ast[0]->expr->items)
            ->filter(fn ($item) => $item?->key instanceof String_)
            ->map(fn ($item) => $item->key->value)
            ->values();

        $config->set('exceptions.not_found', ':Entity does not exist');

        $config->write();

        if (!$configKeys->contains('exceptions')) {
            $this->insertTranslationsEmptyLine('exceptions');
        }
    }

    protected function insertTranslationsEmptyLine(string $key): void
    {
        $content = file_get_contents($this->translationPath);

        $newContent = preg_replace("/\n(\s*)'{$key}'\s*=>/", "\n\n$1'{$key}' =>", $content, 1);

        file_put_contents($this->translationPath, $newContent);
    }
}
