<?php

namespace App\Services\Geo;

use Exception;

/**
 * Minimal ESRI shapefile reader for cadastral plot uploads.
 *
 * Reads polygon geometry from the .shp, attributes from the sibling .dbf,
 * and reprojects UTM coordinates to WGS84 lat/lng using the .prj definition
 * (Tanzanian cadastral data is normally WGS84 / UTM zone 35S-37S).
 */
class EsriShapefileParser
{
    private const WGS84_A = 6378137.0;

    private const WGS84_F = 1 / 298.257223563;

    /**
     * @return array{type: string, features: array<int, array<string, mixed>>}
     */
    public function parse(string $shpPath, ?string $dbfPath = null, ?string $prjPath = null): array
    {
        $records = $this->readShapeRecords($shpPath);
        $attributes = $dbfPath && is_file($dbfPath) ? $this->readDbfRecords($dbfPath) : [];
        $projection = $prjPath && is_file($prjPath) ? $this->readProjection($prjPath) : null;

        $features = [];
        foreach ($records as $index => $rings) {
            $coordinates = [];
            foreach ($rings as $ring) {
                $coordinates[] = array_map(function (array $point) use ($projection) {
                    [$x, $y] = $point;
                    if ($projection) {
                        [$lng, $lat] = $this->inverseTransverseMercator($x, $y, $projection);
                    } else {
                        [$lng, $lat] = [$x, $y];
                    }

                    return [round($lng, 7), round($lat, 7)];
                }, $ring);
            }

            $features[] = [
                'type' => 'Feature',
                'properties' => $this->canonicalProperties($attributes[$index] ?? [], $index),
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => $coordinates,
                ],
            ];
        }

        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    /**
     * Read polygon records (shape types 5, 15, 25) as arrays of rings of [x, y].
     *
     * @return array<int, array<int, array<int, array{0: float, 1: float}>>>
     */
    private function readShapeRecords(string $shpPath): array
    {
        $data = file_get_contents($shpPath);
        if ($data === false || strlen($data) < 100) {
            throw new Exception('Unreadable or empty .shp file.');
        }

        $fileCode = unpack('N', substr($data, 0, 4))[1];
        if ($fileCode !== 9994) {
            throw new Exception('Not a valid ESRI shapefile.');
        }

        $length = strlen($data);
        $offset = 100;
        $records = [];

        while ($offset + 12 <= $length) {
            $contentWords = unpack('N', substr($data, $offset + 4, 4))[1];
            $contentBytes = $contentWords * 2;
            $shapeType = unpack('V', substr($data, $offset + 8, 4))[1];
            $body = substr($data, $offset + 12, $contentBytes - 4);
            $offset += 8 + $contentBytes;

            if (! in_array($shapeType, [5, 15, 25], true)) {
                continue; // Null shapes or unsupported types are skipped.
            }

            // Polygon body: box (32) + numParts (4) + numPoints (4) + parts + points.
            $numParts = unpack('V', substr($body, 32, 4))[1];
            $numPoints = unpack('V', substr($body, 36, 4))[1];

            $parts = [];
            for ($p = 0; $p < $numParts; $p++) {
                $parts[] = unpack('V', substr($body, 40 + $p * 4, 4))[1];
            }
            $parts[] = $numPoints;

            $pointsBase = 40 + $numParts * 4;
            $rings = [];
            for ($p = 0; $p < $numParts; $p++) {
                $ring = [];
                for ($i = $parts[$p]; $i < $parts[$p + 1]; $i++) {
                    $pair = unpack('ex/ey', substr($body, $pointsBase + $i * 16, 16));
                    $ring[] = [$pair['x'], $pair['y']];
                }
                $rings[] = $ring;
            }

            $records[] = $rings;
        }

        return $records;
    }

    /**
     * Read dBase III attribute rows keyed by record index.
     *
     * @return array<int, array<string, mixed>>
     */
    private function readDbfRecords(string $dbfPath): array
    {
        $data = file_get_contents($dbfPath);
        if ($data === false || strlen($data) < 32) {
            return [];
        }

        $numRecords = unpack('V', substr($data, 4, 4))[1];
        $headerSize = unpack('v', substr($data, 8, 2))[1];
        $recordSize = unpack('v', substr($data, 10, 2))[1];

        $fields = [];
        for ($pos = 32; $pos < $headerSize - 1; $pos += 32) {
            if ($data[$pos] === "\x0d") {
                break;
            }
            $descriptor = substr($data, $pos, 32);
            $fields[] = [
                'name' => strtolower(rtrim(substr($descriptor, 0, 11), "\x00")),
                'type' => $descriptor[11],
                'length' => ord($descriptor[16]),
            ];
        }

        $rows = [];
        for ($r = 0; $r < $numRecords; $r++) {
            $recordOffset = $headerSize + $r * $recordSize;
            $record = substr($data, $recordOffset, $recordSize);
            if ($record === '' || $record[0] === '*') {
                continue; // deleted row
            }

            $cursor = 1;
            $row = [];
            foreach ($fields as $field) {
                $raw = trim(substr($record, $cursor, $field['length']));
                $cursor += $field['length'];
                $row[$field['name']] = match ($field['type']) {
                    'N', 'F' => $raw === '' ? null : (float) $raw,
                    'L' => in_array(strtoupper($raw), ['T', 'Y'], true),
                    default => mb_convert_encoding($raw, 'UTF-8', 'UTF-8, ISO-8859-1'),
                };
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Map raw cadastral attribute names onto the keys the platform expects,
     * keeping every original attribute alongside.
     *
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    private function canonicalProperties(array $attrs, int $index): array
    {
        $pick = function (array $candidates) use ($attrs) {
            foreach ($candidates as $key) {
                if (isset($attrs[$key]) && $attrs[$key] !== '' && $attrs[$key] !== null) {
                    return $attrs[$key];
                }
            }

            return null;
        };

        $area = $pick(['area_sqm', 'legal_area', 'area', 'shape_area', 'sqm', 'size_sqm', 'plot_size']);

        return array_merge($attrs, array_filter([
            'parcel_number' => $pick(['parcel_number', 'plot_no', 'plot_num', 'plotno', 'plot', 'pl_no', 'parcel', 'plot_id', 'lot_number', 'lot_no']) ?? (string) ($index + 1),
            'block_number' => $pick(['block_number', 'block_no', 'block', 'blk', 'blockno', 'block_numb']),
            'area_sqm' => is_numeric($area) ? round((float) $area, 2) : null,
            'land_use' => $pick(['land_use', 'landuse', 'use', 'zoning']),
            'status' => $this->normaliseStatus($pick(['status', 'state', 'avail', 'allocation'])),
            'price' => $pick(['price', 'amount', 'cost']),
        ], fn ($v) => $v !== null));
    }

    private function normaliseStatus(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return match (strtolower($value)) {
            'vacant', 'available', 'open' => 'Available',
            'allocated', 'reserved', 'pending' => 'Reserved',
            'sold', 'taken', 'occupied' => 'Sold',
            default => $value,
        };
    }

    /**
     * Extract Transverse Mercator parameters from a .prj WKT definition.
     * Returns null when the data is already geographic (degrees).
     *
     * @return array{lon0: float, lat0: float, k0: float, fe: float, fn: float}|null
     */
    private function readProjection(string $prjPath): ?array
    {
        $wkt = (string) file_get_contents($prjPath);

        if (stripos($wkt, 'Transverse_Mercator') === false && stripos($wkt, 'UTM') === false) {
            return null; // Assume geographic coordinates.
        }

        $param = function (string $name, float $default) use ($wkt): float {
            return preg_match('/PARAMETER\s*\[\s*"'.$name.'"\s*,\s*(-?[0-9.]+)/i', $wkt, $m) ? (float) $m[1] : $default;
        };

        return [
            'lon0' => deg2rad($param('Central_Meridian', 33.0)),
            'lat0' => deg2rad($param('Latitude_Of_Origin', 0.0)),
            'k0' => $param('Scale_Factor', 0.9996),
            'fe' => $param('False_Easting', 500000.0),
            'fn' => $param('False_Northing', 10000000.0),
        ];
    }

    /**
     * Inverse Transverse Mercator on the WGS84 ellipsoid (Snyder 1987, eq. 8-17..8-25).
     *
     * @param  array{lon0: float, lat0: float, k0: float, fe: float, fn: float}  $p
     * @return array{0: float, 1: float} [lng, lat] in degrees
     */
    private function inverseTransverseMercator(float $x, float $y, array $p): array
    {
        $a = self::WGS84_A;
        $f = self::WGS84_F;
        $e2 = $f * (2 - $f);
        $ep2 = $e2 / (1 - $e2);

        $m0 = $this->meridionalArc($p['lat0'], $a, $e2);
        $m = $m0 + ($y - $p['fn']) / $p['k0'];
        $mu = $m / ($a * (1 - $e2 / 4 - 3 * $e2 ** 2 / 64 - 5 * $e2 ** 3 / 256));

        $e1 = (1 - sqrt(1 - $e2)) / (1 + sqrt(1 - $e2));
        $phi1 = $mu
            + (3 * $e1 / 2 - 27 * $e1 ** 3 / 32) * sin(2 * $mu)
            + (21 * $e1 ** 2 / 16 - 55 * $e1 ** 4 / 32) * sin(4 * $mu)
            + (151 * $e1 ** 3 / 96) * sin(6 * $mu)
            + (1097 * $e1 ** 4 / 512) * sin(8 * $mu);

        $sin1 = sin($phi1);
        $cos1 = cos($phi1);
        $tan1 = tan($phi1);

        $c1 = $ep2 * $cos1 ** 2;
        $t1 = $tan1 ** 2;
        $n1 = $a / sqrt(1 - $e2 * $sin1 ** 2);
        $r1 = $a * (1 - $e2) / (1 - $e2 * $sin1 ** 2) ** 1.5;
        $d = ($x - $p['fe']) / ($n1 * $p['k0']);

        $lat = $phi1 - ($n1 * $tan1 / $r1) * (
            $d ** 2 / 2
            - (5 + 3 * $t1 + 10 * $c1 - 4 * $c1 ** 2 - 9 * $ep2) * $d ** 4 / 24
            + (61 + 90 * $t1 + 298 * $c1 + 45 * $t1 ** 2 - 252 * $ep2 - 3 * $c1 ** 2) * $d ** 6 / 720
        );

        $lng = $p['lon0'] + (
            $d
            - (1 + 2 * $t1 + $c1) * $d ** 3 / 6
            + (5 - 2 * $c1 + 28 * $t1 - 3 * $c1 ** 2 + 8 * $ep2 + 24 * $t1 ** 2) * $d ** 5 / 120
        ) / $cos1;

        return [rad2deg($lng), rad2deg($lat)];
    }

    private function meridionalArc(float $lat, float $a, float $e2): float
    {
        return $a * (
            (1 - $e2 / 4 - 3 * $e2 ** 2 / 64 - 5 * $e2 ** 3 / 256) * $lat
            - (3 * $e2 / 8 + 3 * $e2 ** 2 / 32 + 45 * $e2 ** 3 / 1024) * sin(2 * $lat)
            + (15 * $e2 ** 2 / 256 + 45 * $e2 ** 3 / 1024) * sin(4 * $lat)
            - (35 * $e2 ** 3 / 3072) * sin(6 * $lat)
        );
    }
}
