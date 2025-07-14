<?php

// app/Http/Controllers/SchoolStructureController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SchoolStructureController extends Controller
{
    public function getStructureByCurriculumKey($curriculumKey)
    {
        $paths = [
            1 => resource_path('js/data/curriculums/cbc.json'),
            2 => resource_path('js/data/curriculums/844.json'),
            3 => resource_path('js/data/curriculums/igcse.json'),
            4 => resource_path('js/data/curriculums/alevel.json'),
        ];

        if (!isset($paths[$curriculumKey]) || !file_exists($paths[$curriculumKey])) {
            return response()->json(['error' => 'Unknown curriculum key'], 404);
        }

        $levels = json_decode(file_get_contents($paths[$curriculumKey]), true);
        $grouped = [];

        foreach ($levels as $item) {
            $stage = $item['stage'];
            $level = $item['level'];

            if (!isset($grouped[$stage])) {
                $grouped[$stage] = ['levels' => []];
            }

            $grouped[$stage]['levels'][$level] = $item;
        }

        return response()->json([
            'system' => 'CBC',
            'stages' => $grouped
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:school_structures,id',
            'structure' => 'required|array',
        ]);

        $structure = \App\Models\SchoolStructure::findOrFail($request->id);
        $structure->structure = $request->structure;
        $structure->save();

        return response()->json(['success' => true, 'structure' => $structure]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'structure' => 'required|array',
        ]);

        $user = $request->user();
        $school = $user->school;
        if (!$school) {
            return response()->json(['error' => 'No school found for user'], 400);
        }

        $newStructure = $school->structures()->create([
            'title' => $request->title,
            'structure' => $request->structure,
        ]);

        return response()->json(['success' => true, 'structure' => $newStructure]);
    }
}
