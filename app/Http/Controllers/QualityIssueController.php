<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\QualityIssue;
use App\Models\User;
use Illuminate\Http\Request;

class QualityIssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $qualityIssues = QualityIssue::all();

        return response()->json([
            'success' => true,
            'quality_issues' => $qualityIssues,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'issue_id' => 'required|exists:issues,id',
            'user_id' => 'required|exists:users,id', // Add this line
            'problem' => 'required|string',
            'machine_performance' => 'required|numeric|between:0,100',
            'trouble_duration_minutes' => 'required|numeric|min:0',
            'solution' => 'required|string',
            'impact' => 'required|string',
        ]);

        $qualityIssue = QualityIssue::create([
            'issue_id' => $request->issue_id,
            'user_id' => $request->user_id, // Add this line
            'problem' => $request->problem,
            'machine_performance' => $request->machine_performance,
            'trouble_duration_minutes' => $request->trouble_duration_minutes,
            'solution' => $request->solution,
            'impact' => $request->impact,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quality issue created successfully.',
            'quality_issue' => $qualityIssue,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\QualityIssue  $qualityIssue
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(QualityIssue $qualityIssue)
    {
        $issue = Issue::findOrFail($qualityIssue->issue_id);
        $user = User::findOrFail($qualityIssue->user_id);
        return response()->json([
            'issue' => [
                'shift' => $issue->shift,
            ],
            'success' => true,
            'quality_issue' => [
                'id' => $qualityIssue->id,
                'problem' => $qualityIssue->problem,
                'machine_performance' => $qualityIssue->machine_performance,
                'trouble_duration_minutes' => $qualityIssue->trouble_duration_minutes,
                'solution' => $qualityIssue->solution,
                'impact' => $qualityIssue->impact,
                'user_id' => $qualityIssue->user_id,
                'shift' => $issue->shift,
                'issue_date' => $issue->issue_date,
                'user_name' => $user->name,
                'created_at' => $qualityIssue->created_at
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\QualityIssue  $qualityIssue
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, QualityIssue $qualityIssue)
    {
        $request->validate([
            'problem' => 'string',
            'machine_performance' => 'numeric|between:0,100',
            'trouble_duration_minutes' => 'numeric|min:0',
            'solution' => 'string',
            'impact' => 'string',
        ]);

        $qualityIssue->update($request->only([
            'problem', 'machine_performance', 'trouble_duration_minutes', 'solution', 'impact'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Quality issue updated successfully.',
            'quality_issue' => $qualityIssue,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\QualityIssue  $qualityIssue
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(QualityIssue $qualityIssue)
    {
        $qualityIssue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quality issue deleted successfully.',
        ], 200);
    }

    /**
     * Mengambil semua data quality issue berdasarkan issue id.
     *
     * @param int $issueId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQualityIssuesByIssueId($issueId)
    {
        $qualityIssues = QualityIssue::where('issue_id', $issueId)->get();

        // Retrieve the related issue information
        $issue = Issue::find($issueId);

        return response()->json([
            'success' => true,
            'issue' => $issue,
            'quality_issues' => $qualityIssues,
        ], 200);
    }
}
