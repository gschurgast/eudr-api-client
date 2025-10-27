<?php

declare(strict_types=1);

namespace src\Enum;

/**
 * Represents the target environment for the EUDR SOAP services.
 */
enum GeometryTypeEnum: string
{
    case POINT           = 'Point';
    case MULTIPOINT      = 'MultiPoint';
    case LINESTRING      = 'LineString';
    case MULTILINESTRING = 'MultiLineString';
    case POLYGON         = 'Polygon';
    case MULTIPOLYGON    = 'MultiPolygon';
}
