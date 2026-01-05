<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function show()
    {
        $settings = CompanySetting::first();
        if (!$settings) {
            $settings = CompanySetting::create([
                'company_name' => 'My Company',
            ]);
        }
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $settings = CompanySetting::first();
        if (!$settings) {
            $settings = new CompanySetting();
        }

        $request->validate([
            'company_name' => 'required|string',
            'tin_number' => 'nullable|string',
            'p_o_box' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'logo' => 'nullable|image|max:2048',
            'stamp' => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'company_name', 'tin_number', 'p_o_box', 'address', 'phone', 'email'
        ]);

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('company_logos', 'public');
        }

        if ($request->hasFile('stamp')) {
            if ($settings->stamp_path) {
                Storage::disk('public')->delete($settings->stamp_path);
            }
            $data['stamp_path'] = $request->file('stamp')->store('company_stamps', 'public');
        }

        $settings->fill($data);
        $settings->save();

        return response()->json([
            'message' => 'Company settings updated successfully',
            'settings' => $settings
        ]);
    }
}
