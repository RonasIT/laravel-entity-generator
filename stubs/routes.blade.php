
Route::post('/{{$entities}}', ['uses' => {{$entity}}Controller::class . '@create']);
Route::put('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@update']);
Route::delete('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@delete']);
Route::get('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@get']);
Route::get('/{{$entities}}', ['uses' => {{$entity}}Controller::class . '@search']);
