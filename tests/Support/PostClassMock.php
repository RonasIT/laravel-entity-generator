<?php

namespace RonasIT\Support\Tests\Support;

use App\Nova\Actions\PublishPostAction;
use App\Nova\Actions\UnPublishPostAction;
use Laravel\Nova\Http\Requests\NovaRequest;

class PostClassMock
{
    public function actions()
    {
        return [
            PublishPostAction::make()
                ->canSee(function () {
                    return true;
                }),
            UnPublishPostAction::make()
                ->canSee(function () {
                    return true;
                })
                ->canRun(function (NovaRequest $request, $post) {
                    return $request->user()->can('publish', $post);
                }),
            (new PublishPostAction)
                ->canSee(function () {
                    return true;
                }),
            new UnPublishPostAction
        ];
    }
}
