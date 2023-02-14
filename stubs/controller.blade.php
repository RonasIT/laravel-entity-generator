namespace App\Http\Controllers;

use App\Http\Resources\{{$entity}}Resource;
@if (in_array('C', $options))
use App\Http\Requests\{{$requestsFolder}}\Create{{$entity}}Request;
@endif
@if (in_array('U', $options))
use App\Http\Requests\{{$requestsFolder}}\Update{{$entity}}Request;
@endif
@if (in_array('D', $options))
use App\Http\Requests\{{$requestsFolder}}\Delete{{$entity}}Request;
@endif
@if (in_array('R', $options))
use App\Http\Requests\{{$requestsFolder}}\Get{{$entity}}Request;
use App\Http\Requests\{{$requestsFolder}}\Search{{\Illuminate\Support\Str::plural($entity)}}Request;
@endif
use App\Services\{{$entity}}Service;
@if (in_array('D', $options) || in_array('U', $options))
use Symfony\Component\HttpFoundation\Response;

@endif
class {{$entity}}Controller extends Controller
{
@if (in_array('C', $options))
    public function create(Create{{$entity}}Request $request, {{$entity}}Service $service)
    {
        $data = $request->onlyValidated();

        $result = $service->create($data);

        return {{$entity}}Resource::make($result);
    }

@endif
@if (in_array('R', $options))
    public function get(Get{{$entity}}Request $request, {{$entity}}Service $service, $id)
    {
        $result = $service
            ->with($request->input('with', []))
            ->withCount($request->input('with_count', []))
            ->find($id);

        return {{$entity}}Resource::make($result);
    }

    public function search(Search{{\Illuminate\Support\Str::plural($entity)}}Request $request, {{$entity}}Service $service)
    {
        $result = $service->search($request->onlyValidated());

        return {{$entity}}Resource::make($result);
    }

@endif
@if (in_array('U', $options))
    public function update(Update{{$entity}}Request $request, {{$entity}}Service $service, $id)
    {
        $service->update($id, $request->onlyValidated());

        return response('', Response::HTTP_NO_CONTENT);
    }

@endif
@if (in_array('D', $options))
    public function delete(Delete{{$entity}}Request $request, {{$entity}}Service $service, $id)
    {
        $service->delete($id);

        return response('', Response::HTTP_NO_CONTENT);
    }
@endif
}