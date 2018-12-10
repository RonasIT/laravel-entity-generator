namespace App\Http\Controllers;

use App\Http\Requests\{{$requestsFolder}}\Create{{$entity}}Request;
use App\Http\Requests\{{$requestsFolder}}\Get{{$entity}}Request;
use App\Http\Requests\{{$requestsFolder}}\Update{{$entity}}Request;
use App\Http\Requests\{{$requestsFolder}}\Delete{{$entity}}Request;
use App\Http\Requests\{{$requestsFolder}}\Search{{$entity}}Request;
use App\Services\{{$entity}}Service;
use Symfony\Component\HttpFoundation\Response;

class {{$entity}}Controller extends Controller
{
    public function create(Create{{$entity}}Request $request, {{$entity}}Service $service)
    {
        $data = $request->all();

        $result = $service->create($data);

        return response()->json($result);
    }

    public function get(Get{{$entity}}Request $request, {{$entity}}Service $service, $id)
    {
        $result = $service
            ->withRelations($request->input('with', []))
            ->find($id);

        return response()->json($result);
    }

    public function update(Update{{$entity}}Request $request, {{$entity}}Service $service, $id)
    {
        $service->update(
            ['id' => $id],
            $request->all()
        );

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function delete(Delete{{$entity}}Request $request, {{$entity}}Service $service, $id)
    {
        $service->delete(['id' => $id]);

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function search(Search{{$entity}}Request $request, {{$entity}}Service $service)
    {
        $result = $service->search($request->all());

        return response($result);
    }
}