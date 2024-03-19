<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Issue;
use App\Models\QualityIssue;

class TrackRecordIssueController extends Controller
{
    /**
     * Retrieve all issues with related quality issues, sorted by issue date in descending order.
     * Optionally filter issues based on provided problem.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Mengambil query parameter 'problem' jika ada
        $problem = $request->input('problem');

        // Mengambil semua isu dengan relasi kualitas isu
        $query = QualityIssue::select(
            'quality_issues.id',
            'quality_issues.closed',
            'quality_issues.closed_date',
            'quality_issues.todos',
            'quality_issues.quality_control_verification',
            'quality_issues.created_at',
            'quality_issues.problem',
            'issues.issue_date' // Tambahkan kolom issue_date dari tabel Issue
        )
            ->leftJoin('issues', 'quality_issues.issue_id', '=', 'issues.id') // Join dengan tabel Issue
            ->orderBy('quality_issues.created_at', 'desc'); // Urutkan berdasarkan tanggal pembuatan quality issue

        // Jika ada parameter 'problem', tambahkan filter ke query
        if ($problem) {
            $query->where('quality_issues.problem', 'like', '%' . $problem . '%');
        }

        // Tambahkan kondisi jika closed adalah false
        $query->where('quality_issues.closed', false);

        // Eksekusi query dan kirimkan hasil sebagai respons JSON
        $issues = $query->get();

        return response()->json([
            'success' => true,
            'issues' => $issues,
        ]);
    }


    /**
     * Retrieve details of a specific issue with related quality issues.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Mengambil isu berdasarkan ID dengan relasi kualitas isu
        $issue = QualityIssue::select('id', 'closed', 'closed_date', 'todos', 'quality_control_verification')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'issue' => $issue,
        ]);
    }

    /**
     * Update the specified issue in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi data yang dikirim oleh pengguna
        $request->validate([
            'closed' => 'required|boolean',
            'closed_date' => 'nullable|date',
            'todos' => 'nullable|string',
            'quality_control_verification' => 'nullable|string',
        ]);

        // Cari isu berdasarkan ID
        $issue = QualityIssue::findOrFail($id);

        // Update isu dengan data baru
        $issue->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Quality Issue updated successfully.',
            'issue' => $issue,
        ]);
    }

    /**
     * Toggle the 'closed' status of the specified issue.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleClosed($id)
    {
        // Cari isu berdasarkan ID
        $qualityIssue = QualityIssue::findOrFail($id);

        // Toggle closed status
        $qualityIssue->closed = !$qualityIssue->closed;

        // Jika closed true, atur closed_date ke saat ini
        if ($qualityIssue->closed) {
            $qualityIssue->closed_date = now()->toDateString();
        } else {
            // Jika closed false, atur closed_date menjadi null
            $qualityIssue->closed_date = null;
        }

        // Simpan perubahan
        $qualityIssue->save();

        return response()->json([
            'success' => true,
            'message' => 'Issue closed status toggled successfully.',
            'issue' => $qualityIssue,
        ]);
    }
}
