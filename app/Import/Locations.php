<?php

namespace App\Import;

use Illuminate\Support\Facades\Log;
use App\Http\Api\Import\ESILocationsImportService;
use App\Models\Region;
use App\Models\Constellation;
use App\Models\System;
use App\Models\Stargate;
use App\Models\Station;

class Locations extends AbstractImporter
{
    /** @var ESILocationsImportService $esi */
    private ESILocationsImportService $esi;

    /**
     * Locations constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->esi = app('esi.locations');
    }

    /**
     * Import regions.
     *
     * @return array
     */
    public function regions() {

        $this->esi->setType('regions');
        $regions = $this->esi->getData();

        $total = count($regions);
        $count = 0;
        $errors = 0;
        if ($total > 0)
        {
            foreach ($regions as $id)
            {
                if (! Region::whereRegionId($id)->exists())
                {
                    $data = $this->esi->getData($id);

                    $region = new Region();
                    $region->region_id = $data->region_id;
                    $region->name = $data->name;
                    $region->description = $data->description ?? '';

                    if ($region->save())
                    {
                        ++$count;
                    } else {
                        Log::error('Failed to import region: ' . $id);
                        ++$errors;
                    }
                } else {
                    --$total;
                }
            }
        }

        return [
            'regions' => $total,
            'imported' => $count,
            'errors' => $errors
        ];
    }

    /**
     * Import constellations.
     *
     * @return array
     */
    public function constellations() {
        $this->esi->setType('constellations');

        $constellations = $this->esi->getData();
        $total = count($constellations);
        $count = 0;
        $errors = 0;
        if ($total > 0)
        {
            foreach ($constellations as $id)
            {
                if (! Constellation::whereConstellationId($id)->exists())
                {
                    $data = $this->esi->getData($id);

                    $constellation = new Constellation();
                    $constellation->region_id = $data->region_id;
                    $constellation->constellation_id = $data->constellation_id;
                    $constellation->name = $data->name;

                    if ($constellation->save())
                    {
                        ++$count;
                    } else {
                        Log::error('Failed to import contellation: ' . $id);
                        ++$errors;
                    }
                } else {
                    --$total;
                }
            }
        }

        return [
            'constellations' => $total,
            'imported' => $count,
            'errors' => $errors
        ];
    }

    /**
     * Import systems.
     *
     * @return array
     */
    public function systems() {
        $this->esi->setType('systems');

        $systems = $this->esi->getData();
        $total = count($systems);
        $count = 0;
        $errors = 0;
        if ($total > 0)
        {
            foreach ($systems as $id)
            {
                if (! System::whereSystemId($id)->exists())
                {
                    $data = $this->esi->getData($id);

                    $system = new System();
                    $system->constellation_id = $data->constellation_id;
                    $system->system_id = $data->system_id;
                    $system->name = $data->name;
                    $system->security_class = $data->security_class ?? '';
                    $system->security_status = $data->security_status ?? '';

                    if ($system->save())
                    {
                        ++$count;
                    } else {
                        Log::error('Failed to import system: ' . $id);
                        ++$errors;
                    }
                } else {
                    --$total;
                }
            }
        }

        return [
            'systems' => $total,
            'imported' => $count,
            'errors' => $errors
        ];
    }

    /**
     * Import stargates.
     *
     */
    public function stargates() {
    }

    /**
     * Import stations.
     *
     */
    public function stations() {}
}
