<?php

namespace App\Http\Controllers\Admin;

use App\Models\TravelStyle;
use Illuminate\Http\Request;

/** Admin > Travel styles: Slow, Scenic, Luxury... Packages are ticked into styles on the package form. */
class TravelStyleController extends TaxonomyController
{
    protected string $model = TravelStyle::class;

    protected string $routeBase = 'admin.travel-styles';

    protected string $label = 'Travel style';

    protected array $extra = ['tagline', 'highlights'];

    public function edit(TravelStyle $travelStyle) { return $this->form($travelStyle); }

    public function update(Request $request, TravelStyle $travelStyle) { return $this->update_($request, $travelStyle); }

    /** Packages only lose the link to this style; the packages themselves stay. */
    public function destroy(TravelStyle $travelStyle)
    {
        $travelStyle->packages()->detach();
        $travelStyle->delete();

        return redirect()->route($this->routeBase.'.index')->with('status', 'Travel style deleted.');
    }
}
