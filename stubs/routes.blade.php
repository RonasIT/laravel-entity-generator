
Route::post('/{{$entities}}', ['uses' => {{$entity}}Controller::class.'@create'])->middleware('auth:api');
Route::put('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class.'@update'])->middleware('auth:api');
Route::delete('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class.'@delete'])->middleware('auth:api');
Route::get('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class.'@get']);
Route::get('/{{$entities}}', ['uses' => {{$entity}}Controller::class.'@search']);