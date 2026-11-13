<?php

namespace App\Http\Controllers\Admin;

use App\Models\TourType;
use Illuminate\Http\Request;

class TourTypeController extends TaxonomyController
{
    protected string $model = TourType::class;

    protected string $routeBase = 'admin.tour-types';

    protected string $label = 'Tour type';

    protected bool $hasIcon = true;

    public function edit(TourType $tourType) { return $this->form($tourType); }

    public function update(Request $request, TourType $tourType) { return $this->update_($request, $tourType); }

    public function destroy(TourType $tourType) { return $this->destroy_($tourType); }
}
