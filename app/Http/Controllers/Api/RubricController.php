<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rubric;
use App\Models\Stage;
use Illuminate\Http\Request;
use App\Traits\Cacheable;

class RubricController extends Controller
{
    use Cacheable;

    public function getByStage($stageId)
    {
        $rubrics = $this->rememberRubric($stageId);
        return response()->json($rubrics);
    }

    public function store(Request $request)
    {
        $this->authorize('admin');
        $request->validate([
            'stage_id' => 'required|exists:stages,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);
        $rubric = Rubric::create($request->all());
        $this->forgetRubric($request->stage_id);
        return response()->json($rubric, 201);
    }

    public function update(Request $request, Rubric $rubric)
    {
        $this->authorize('admin');
        $oldStageId = $rubric->stage_id;
        $rubric->update($request->only(['name', 'description']));
        $this->forgetRubric($oldStageId);
        if ($rubric->stage_id != $oldStageId) {
            $this->forgetRubric($rubric->stage_id);
        }
        return response()->json($rubric);
    }

    public function destroy(Rubric $rubric)
    {
        $this->authorize('admin');
        $stageId = $rubric->stage_id;
        $rubric->delete();
        $this->forgetRubric($stageId);
        return response()->json(['message' => 'Rubric deleted']);
    }
}