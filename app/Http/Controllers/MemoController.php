<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MemoController extends Controller
{
    /**
     * Display a listing of the memos.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $memos = Memo::all();

        return response()->json([
            'success' => true,
            'memos' => $memos,
        ], 200);
    }

    /**
     * Store a newly created memo in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'issue_id' => 'required|exists:issues,id',
            'title' => 'required|string',
            'memo' => 'required|string',
        ]);

        $memo = Memo::create($validatedData);

        return response()->json([
            'success' => true,
            'memo' => $memo,
        ], 201);
    }

    /**
     * Display the specified memo.
     *
     * @param Memo $memo
     * @return JsonResponse
     */
    public function show(Memo $memo)
    {
        return response()->json([
            'success' => true,
            'memo' => $memo,
        ], 200);
    }

    /**
     * Update the specified memo in storage.
     *
     * @param Request $request
     * @param Memo $memo
     * @return JsonResponse
     */
    public function update(Request $request, Memo $memo)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'memo' => 'required|string',
        ]);

        $memo->update($validatedData);

        return response()->json([
            'success' => true,
            'memo' => $memo,
        ], 200);
    }

    /**
     * Remove the specified memo from storage.
     *
     * @param Memo $memo
     * @return JsonResponse
     */
    public function destroy(Memo $memo)
    {
        $memo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Memo deleted successfully.',
        ], 200);
    }

    /**
     * Menampilkan daftar memo berdasarkan issue_id.
     *
     * @param int $issueId
     * @return JsonResponse
     */
    public function getMemosByIssueId($issueId)
    {
        $memos = Memo::where('issue_id', $issueId)->get();

        return response()->json([
            'success' => true,
            'memos' => $memos,
        ], 200);
    }
}
