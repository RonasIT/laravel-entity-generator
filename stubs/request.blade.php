@inject('requestsGenerator', 'RonasIT\EntityGenerator\Generators\RequestsGenerator')
namespace {{ $namespace }}\{{ $requestsFolder }};

use {{ $namespace }}\Request;
@if($needToValidate)
use {{ $servicesNamespace }}\{{ $entity }}Service;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
@endif
@if($method === $requestsGenerator::SEARCH_METHOD)
use {{ $entityNamespace }};
@endif

class {{ $method }}{{ $entity }}Request extends Request
{
@if($method !== $requestsGenerator::DELETE_METHOD)
    public function rules(): array
    {
@if(!empty($parameters))
@if($needToValidateWith)
        $availableRelations = implode(',', $this->getAvailableRelations());

@endif
        return [
@foreach($parameters as $name => $rules)
            '{{ $name }}' => '{!! implode('|', $rules) !!}'@if(!empty($dynamicRules) && Arr::exists($dynamicRules, $name)) . {!! $dynamicRules[$name] !!}@endif,
@endforeach
        ];
@else
        return [];
@endif
    }
@endif
@if($method !== $requestsGenerator::DELETE_METHOD && $needToValidate)

@endif
@if($needToValidate)
    public function validateResolved(): void
    {
        parent::validateResolved();

        $service = app({{ $entity }}Service::class);

        if (!$service->exists($this->route('id'))) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => '{{ $entity }}']));
        }
    }
@endif
@if($needToValidateWith)

    //TODO: don't forget to review relations list
    protected function getAvailableRelations(): array
    {
@if(!empty($availableRelations))
        return [
@foreach($availableRelations as $relation)
            '{{ $relation }}',
@endforeach
        ];
@else
        return [];
@endif
    }
@endif
}