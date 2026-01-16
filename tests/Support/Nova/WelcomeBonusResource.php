<?php

namespace App\Nova;

use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\PublishPostAction;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\UnPublishPostAction;

class WelcomeBonusResource extends BaseTestResource
{
    public function actions(NovaRequest $request): array
    {
        return [
            new PublishPostAction(),
            new UnPublishPostAction(),
            new UnPublishPostAction(),
        ];
    }
}
