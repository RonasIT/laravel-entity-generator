@if (in_array('C', $options))
    Route::post('/{{$entities}}', [{{$entity}}Controller::class, 'create']);
@endif
@if (in_array('U', $options))
    Route::put('/{{$entities}}/{id}', [{{$entity}}Controller::class, 'update']);
@endif
@if (in_array('D', $options))
    Route::delete('/{{$entities}}/{id}', [{{$entity}}Controller::class, 'delete']);
@endif
@if (in_array('R', $options))
    Route::get('/{{$entities}}/{id}', [{{$entity}}Controller::class, 'get']);
    Route::get('/{{$entities}}', [{{$entity}}Controller::class, 'search']);
@endif