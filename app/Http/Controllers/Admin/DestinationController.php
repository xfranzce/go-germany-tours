<?php

namespace App\Http\Controllers\Admin;

use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends TaxonomyController
{
    protected string $model = Destination::class;

    protected string $routeBase = 'admin.destinations';

    protected string $label = 'Destination';

    protected array $extra = ['tagline', 'places'];

    public function edit(Destination $destination) { return $this->form($destination); }

    public function update(Request $request, Destination $destination) { return $this->update_($request, $destination); }

    public function destroy(Destination $destination) { return $this->destroy_($destination); }
}
