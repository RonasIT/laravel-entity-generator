namespace App\Http\Requests;
@if($needToValidate)

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\{{$entity}}Service;
@endif

class {{$method}}{{$entity}}Request extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
@if(!empty($parameters))
        return [
@foreach($parameters as $parameter)
            '{{$parameter['name']}}' => '{{implode('|', $parameter['rules'])}}',
@endforeach
        ];
@else
        return [];
@endif
    }

@if($needToValidate)
    public function validate()
    {
        parent::validate();

        $service = app({{$entity}}Service::class);

        if (!$service->exists(['id' => $this->route('id')])) {
            throw new NotFoundHttpException('{{$entity}} does not exists');
        }
    }
@endif
}
