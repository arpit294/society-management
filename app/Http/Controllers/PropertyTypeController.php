<?php

namespace App\Http\Controllers;

use App\Models\PropertyType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PropertyTypeController extends Controller
{
    /**
     * Store a newly created property type.
     */
    public function store(Request $request)
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);

        $request->validate([
            'name' => 'required|string|max:255|unique:property_types,name',
        ]);

        $code = Str::slug($request->name, '_');
        
        // Ensure code uniqueness
        if (PropertyType::where('code', $code)->exists()) {
            $code = $code . '_' . time();
        }

        PropertyType::create([
            'name' => $request->name,
            'code' => $code,
        ]);

        return redirect()->back()->with('success', 'Property Type added successfully.');
    }

    /**
     * Update the specified property type.
     */
    public function update(Request $request, PropertyType $propertyType)
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);

        $request->validate([
            'name' => 'required|string|max:255|unique:property_types,name,' . $propertyType->id,
        ]);

        $propertyType->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Property Type updated successfully.');
    }

    /**
     * Remove the specified property type.
     */
    public function destroy(PropertyType $propertyType)
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);

        $currentSelected = Setting::get('society_property_type', 'flat_residential');
        if ($propertyType->code === $currentSelected) {
            return redirect()->back()->with('error', 'Cannot delete the currently active Society Property Type.');
        }

        $propertyType->delete();

        return redirect()->back()->with('success', 'Property Type deleted successfully.');
    }
}
