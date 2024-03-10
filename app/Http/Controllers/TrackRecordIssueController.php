<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Issue;

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
        $query = Issue::with(['qualityIssues:id,issue_id,problem'])
            ->select('id', 'issue_date', 'closed', 'closed_date', 'todos', 'quality_control_verification')
            ->orderBy('issue_date', 'desc');

        // Jika ada parameter 'problem', tambahkan filter ke query
        if ($problem) {
            $query->whereHas('qualityIssues', function ($q) use ($problem) {
                $q->where('problem', 'like', '%' . $problem . '%');
            });
        }

        // Tambahkan kondisi jika closed adalah false
        $query->where('closed', false);

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
        $issue = Issue::select('id', 'issue_date', 'closed', 'closed_date', 'todos', 'quality_control_verification')
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
        $issue = Issue::findOrFail($id);

        // Update isu dengan data baru
        $issue->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Issue updated successfully.',
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
        $issue = Issue::findOrFail($id);

        // Toggle closed status
        $issue->closed = !$issue->closed;

        // Jika closed true, atur closed_date ke saat ini
        if ($issue->closed) {
            $issue->closed_date = now()->toDateString();
        } else {
            // Jika closed false, atur closed_date menjadi null
            $issue->closed_date = null;
        }

        // Simpan perubahan
        $issue->save();

        return response()->json([
            'success' => true,
            'message' => 'Issue closed status toggled successfully.',
            'issue' => $issue,
        ]);
    }
}
