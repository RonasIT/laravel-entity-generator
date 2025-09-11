<?php

namespace RonasIT\Support\Enums;

enum ResourceTypeEnum: string
{
    case Controller = 'controller';
    case Factory = 'factory';
    case Model = 'model';
    case NovaResource = 'nova resource';
    case NovaTest = 'nova test';
    case Resource = 'resource';
    case CollectionResource = 'collection resource';
}