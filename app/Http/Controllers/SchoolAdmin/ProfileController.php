<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        
        if (!$school) {
            return back()->withErrors(['school' => 'No school linked to this user']);
        }

        return Inertia::render('SchoolAdmin/SchoolProfileSettings', [
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'slogan' => $school->slogan,
                'brand_color' => $school->brand_color,
                'logo' => $school->logo ? Storage::url($school->logo) : null,
                'background' => $school->background ? Storage::url($school->background) : null,
            ]
        ]);
    }

    public function updateBranding(Request $request)
    {
        try {
            // Debug: Log what's being received
            Log::info('Branding update request received', [
                'user_id' => auth()->id(),
                'has_logo_file' => $request->hasFile('logo'),
                'has_background_file' => $request->hasFile('background'),
                'logo_file' => $request->file('logo'),
                'background_file' => $request->file('background'),
                'logo_file_type' => $request->file('logo') ? get_class($request->file('logo')) : 'null',
                'background_file_type' => $request->file('background') ? get_class($request->file('background')) : 'null',
                'logo_is_valid' => $request->file('logo') ? $request->file('logo')->isValid() : false,
                'background_is_valid' => $request->file('background') ? $request->file('background')->isValid() : false,
                'all_inputs' => $request->all()
            ]);

            $school = auth()->user()->school;

            if (!$school) {
                Log::error('School admin attempted to update branding but no school is linked', [
                    'user_id' => auth()->id(),
                    'user_email' => auth()->user()->email
                ]);
                return back()->withErrors(['school' => 'No school linked to this user'])->withInput();
            }

            $updateData = [];

            // Handle text fields if provided
            if ($request->has('name')) {
                $updateData['name'] = $request->input('name');
            }
            if ($request->has('slogan')) {
                $updateData['slogan'] = $request->input('slogan');
            }
            if ($request->has('brand_color')) {
                $updateData['brand_color'] = $request->input('brand_color');
            }

            // Handle logo upload
            if ($request->hasFile('logo') && $request->file('logo') && $request->file('logo')->isValid()) {
                $logoFile = $request->file('logo');
                $logoExtension = strtolower($logoFile->getClientOriginalExtension());
                
                // Validate logo
                if ($logoExtension === 'svg') {
                    $request->validate(['logo' => 'file|max:2048|mimes:svg']);
                } else {
                    $request->validate(['logo' => 'file|image|max:2048|mimes:jpeg,png,jpg,gif,webp']);
                }
                
                // Delete old logo if it exists
                if ($school->logo && Storage::disk('public')->exists($school->logo)) {
                    Storage::disk('public')->delete($school->logo);
                }
                
                // Store new logo
                $logoPath = $logoFile->store('schools/logos', 'public');
                $updateData['logo'] = $logoPath;
                
                Log::info('School logo updated', [
                    'school_id' => $school->id,
                    'school_name' => $school->name,
                    'logo_path' => $logoPath
                ]);
            }

            // Handle background upload
            if ($request->hasFile('background') && $request->file('background') && $request->file('background')->isValid()) {
                $backgroundFile = $request->file('background');
                $backgroundExtension = strtolower($backgroundFile->getClientOriginalExtension());
                
                // Validate background
                if ($backgroundExtension === 'svg') {
                    $request->validate(['background' => 'file|max:4096|mimes:svg']);
                } else {
                    $request->validate(['background' => 'file|image|max:4096|mimes:jpeg,png,jpg,gif,webp']);
                }
                
                // Delete old background if it exists
                if ($school->background && Storage::disk('public')->exists($school->background)) {
                    Storage::disk('public')->delete($school->background);
                }
                
                // Store new background
                $backgroundPath = $backgroundFile->store('schools/backgrounds', 'public');
                $updateData['background'] = $backgroundPath;
                
                Log::info('School background updated', [
                    'school_id' => $school->id,
                    'school_name' => $school->name,
                    'background_path' => $backgroundPath
                ]);
            }

            // Update the school record in the database if there are changes
            if (!empty($updateData)) {
                $school->update($updateData);

                Log::info('School branding updated successfully', [
                    'school_id' => $school->id,
                    'school_name' => $school->name,
                    'updated_fields' => array_keys($updateData)
                ]);
            }

            return back()->with('success', 'School profile updated successfully!');

        } catch (ValidationException $e) {
            Log::warning('School branding validation failed', [
                'user_id' => auth()->id(),
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating school branding', [
                'user_id' => auth()->id(),
                'school_id' => $school->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['general' => 'An error occurred while updating the school profile. Please try again.'])->withInput();
        }
    }

    public function getSchoolProfile()
    {
        $school = auth()->user()->school;
        
        if (!$school) {
            return response()->json(['error' => 'No school linked to this user'], 404);
        }

        return response()->json([
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'slogan' => $school->slogan,
                'brand_color' => $school->brand_color,
                'logo' => $school->logo ? Storage::url($school->logo) : null,
                'background' => $school->background ? Storage::url($school->background) : null,
                'updated_at' => $school->updated_at,
            ]
        ]);
    }
} 