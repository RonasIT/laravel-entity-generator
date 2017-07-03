SET FOREIGN_KEY_CHECKS = 0;
@foreach($truncates as $entity)
truncate table {{$entity}};
@endforeach
SET FOREIGN_KEY_CHECKS = 1;


@foreach($inserts as $entities)
@foreach($entities['items'] as $entity)
INSERT INTO {{$entities['name']}}({!! implode(', ', $entity['fields']) !!}, created_at, updated_at)
    VALUES({!! implode(', ', $entity['values']) !!}, '2016-10-20 11:05:00', '2016-10-20 11:05:00');
@endforeach
@endforeach
