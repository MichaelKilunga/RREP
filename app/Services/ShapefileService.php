<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class ShapefileService
{
    /**
     * Process an uploaded shapefile (.zip), GeoJSON, or KML file and extract standard GeoJSON.
     */
    public function parseUploadedFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'json' || $extension === 'geojson') {
            $content = file_get_contents($file->getRealPath());
            $json = json_decode($content, true);
            if (! is_array($json)) {
                throw new Exception('Invalid GeoJSON file format.');
            }

            return $this->formatGeoJsonFeatures($json);
        }

        if ($extension === 'zip') {
            return $this->parseZipShapefile($file);
        }

        if ($extension === 'kml') {
            return $this->parseKmlFile($file);
        }

        throw new Exception('Unsupported file format. Please upload a .zip (Shapefile), .geojson, or .kml file.');
    }

    /**
     * Extract .zip containing shapefiles (.shp, .dbf, .geojson)
     */
    protected function parseZipShapefile(UploadedFile $file): array
    {
        $zip = new ZipArchive;
        $res = $zip->open($file->getRealPath());

        if ($res !== true) {
            throw new Exception('Failed to extract uploaded ZIP file.');
        }

        $extractPath = storage_path('app/temp/shapefiles/'.uniqid());
        if (! file_exists($extractPath)) {
            mkdir($extractPath, 0777, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Search for .geojson or .json inside zip
        $files = glob($extractPath.'/*.{geojson,json}', GLOB_BRACE);
        if (! empty($files)) {
            $content = file_get_contents($files[0]);
            $json = json_decode($content, true);
            $this->cleanDirectory($extractPath);

            if (is_array($json)) {
                return $this->formatGeoJsonFeatures($json);
            }
        }

        // Parse raw ESRI shapefile components (.shp + .dbf + .prj)
        $shpFiles = glob($extractPath.'/*.shp') ?: glob($extractPath.'/**/*.shp');
        if (! empty($shpFiles)) {
            $shpPath = $shpFiles[0];
            $base = substr($shpPath, 0, -4);
            $dbfPath = is_file($base.'.dbf') ? $base.'.dbf' : null;
            $prjPath = is_file($base.'.prj') ? $base.'.prj' : null;

            try {
                $collection = (new Geo\EsriShapefileParser)->parse($shpPath, $dbfPath, $prjPath);

                return $collection;
            } finally {
                $this->cleanDirectory($extractPath);
            }
        }

        $this->cleanDirectory($extractPath);

        throw new Exception('No shapefile (.shp) or GeoJSON found inside the ZIP archive.');
    }

    /**
     * Parse KML file to GeoJSON features
     */
    protected function parseKmlFile(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            throw new Exception('Invalid KML XML structure.');
        }

        $features = [];
        $placemarks = $xml->xpath('//Placemark');

        foreach ($placemarks as $index => $pm) {
            $name = (string) ($pm->name ?? ('Plot '.($index + 1)));
            $coordsStr = '';

            if (isset($pm->Polygon->outerBoundaryIs->LinearRing->coordinates)) {
                $coordsStr = (string) $pm->Polygon->outerBoundaryIs->LinearRing->coordinates;
            } elseif (isset($pm->Point->coordinates)) {
                $coordsStr = (string) $pm->Point->coordinates;
            }

            $coordinates = [];
            $rawTuples = preg_split('/\s+/', trim($coordsStr));
            foreach ($rawTuples as $tuple) {
                $parts = explode(',', $tuple);
                if (count($parts) >= 2) {
                    $coordinates[] = [(float) $parts[0], (float) $parts[1]];
                }
            }

            if (! empty($coordinates)) {
                $features[] = [
                    'type' => 'Feature',
                    'properties' => [
                        'parcel_number' => $name,
                        'block_number' => 'Block A',
                        'status' => 'Available',
                        'land_use' => 'Makazi / Residential',
                    ],
                    'geometry' => [
                        'type' => count($coordinates) > 1 ? 'Polygon' : 'Point',
                        'coordinates' => count($coordinates) > 1 ? [$coordinates] : $coordinates[0],
                    ],
                ];
            }
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    /**
     * Standardize GeoJSON structure
     */
    public function formatGeoJsonFeatures(array $geojson): array
    {
        if (isset($geojson['type']) && $geojson['type'] === 'FeatureCollection') {
            return $geojson;
        }

        if (isset($geojson['type']) && $geojson['type'] === 'Feature') {
            return [
                'type' => 'FeatureCollection',
                'features' => [$geojson],
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => [],
        ];
    }

    protected function cleanDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.'/'.$item;
            if (is_dir($path)) {
                $this->cleanDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
