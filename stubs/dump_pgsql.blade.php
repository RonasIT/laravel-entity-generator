@foreach($truncates as $entity)
truncate {{$entity}} cascade;
@endforeach

@foreach($inserts as $entities)
@foreach($entities['items'] as $entity)
INSERT INTO {{$entities['name']}}({!! implode(', ', $entity['fields']) !!}, created_at, updated_at)
    VALUES({!! implode(', ', $entity['values']) !!}, '2016-10-20 11:05:00', '2016-10-20 11:05:00');
@endforeach
SELECT setval('{{$entities['name']}}_id_seq', (select max(*) from {{$entities['name']}}));

@endforeach
