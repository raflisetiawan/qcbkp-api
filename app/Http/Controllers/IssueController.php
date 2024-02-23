<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;


use App\Models\Issue;
use App\Models\QualityIssue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class IssueController extends Controller
{
    /**
     * Menampilkan semua isu.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $issues = Issue::all();
        return response()->json([
            'success' => true,
            'issues' => $issues,
        ], 200);
    }

    /**
     * Menampilkan semua isu berdasarkan waktu / issue_date.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getIssuesByDate(Request $request)
    {
        if ($request->has('date')) {
            $request->validate([
                'date' => 'date',
            ]);

            $date = Carbon::parse($request->date)->startOfDay();
        } else {
            // Jika tidak ada tanggal yang diberikan, gunakan tanggal hari ini
            $date = Carbon::now()->startOfDay();
        }

        $issues = Issue::whereDate('issue_date', $date)->with('plant')->when(
            $request->has('plant_id'),
            function ($query) use ($request) {
                $query->where('plant_id', $request->input('plant_id'));
            }
        )->get();

        return response()->json([
            'success' => true,
            'issues' => $issues,
        ], 200);
    }

    /**
     * Menyimpan isu baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift' => [
                'required',
                Rule::unique('issues')->where(function ($query) use ($request) {
                    return $query->where('issue_date', $request->input('issue_date'))
                        ->where('shift', $request->input('shift'))
                        ->where('plant_id', $request->input('plant_id'));
                }),
            ],
            'issue_date' => 'required|date',
            'plant_id' => 'required|exists:plants,id',
            'quality_control_name' => 'nullable|string',
            'qc_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle qc_image
        if ($request->hasFile('qc_image')) {
            $imagePath = $request->file('qc_image')->store('qc_images', 'public');
            $validatedData['qc_image'] = $imagePath;
        }

        $issue = Issue::create($validatedData);

        return response()->json([
            'success' => true,
            'issue' => $issue,
        ], 201);
    }

    /**
     * Menampilkan detail isu berdasarkan ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $issue = Issue::find($id);

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'issue' => $issue,
        ], 200);
    }

    /**
     * Mengambil detail dari tabel issues berdasarkan ID
     * dan mengambil data dari tabel quality_issues berdasarkan ID issues.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getIssueDetails($id)
    {
        $issue = Issue::with('qualityIssues', 'plant')->find($id);

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'issue' => $issue,
        ], 200);
    }

    /**
     * Mengupdate isu berdasarkan ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'exists:users,id',
            'shift' => 'required',
            'issue_date' => 'date',
            'plant_id' => 'exists:plants,id',
            'quality_control_name' => 'nullable|string',
            'qc_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $issue = Issue::find($id);

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found.',
            ], 404);
        }

        // Handle qc_image
        if ($request->hasFile('qc_image')) {
            // Delete existing qc_image if any
            if ($issue->qc_image) {
                Storage::disk('public')->delete($issue->qc_image);
            }

            $imagePath = $request->file('qc_image')->store('qc_images', 'public');
            $validatedData['qc_image'] = $imagePath;
        }

        $issue->update($validatedData);

        return response()->json([
            'success' => true,
            'issue' => $issue,
        ], 200);
    }

    /**
     * Menghapus isu berdasarkan ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $issue = Issue::find($id);

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found.',
            ], 404);
        }

        $issue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Issue deleted successfully.',
        ], 200);
    }

    /**
     * Retrieve the issue_date based on the given ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getIssueDateById($id)
    {
        $issue = Issue::find($id);

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found.',
            ], 404);
        }

        $issueDate = $issue->issue_date;

        return response()->json([
            'success' => true,
            'issue_date' => $issueDate,
        ], 200);
    }

    /**
     * Mengambil data issues dan quality_issues berdasarkan issue id.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIssueAndQualityIssues($id)
    {
        $issue = Issue::with('qualityIssue')->with('plant')->find($id);

        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found.',
            ], 404);
        }

        // Ambil semua quality issues yang terkait dengan issue
        $qualityIssues = QualityIssue::where('issue_id', $id)->get();
        $user = User::findOrFail($issue->user_id);

        // Menambahkan properti quality_issues ke objek issue
        $issue->quality_issues = $qualityIssues;
        $issue->user = $user;

        return response()->json([
            'success' => true,
            'issue' => $issue,
        ], 200);
    }

    /**
     * Mengambil semua issues beserta quality_issues berdasarkan tanggal.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllIssuesWithQualityIssuesByDate(Request $request)
    {
        if ($request->has('date')) {
            $request->validate([
                'date' => 'date',
            ]);

            $date = Carbon::parse($request->date)->startOfDay();
        } else {
            // Jika tidak ada tanggal yang diberikan, gunakan tanggal hari ini
            $date = Carbon::now()->startOfDay();
        }

        // Ambil semua issues beserta quality_issues berdasarkan issue_date
        $issues = Issue::with('qualityIssues', 'user')
            ->whereDate('issue_date', $date)->with('plant')
            ->get();

        return response()->json([
            'success' => true,
            'issues' => $issues, // Mengubah collection ke array
        ], 200);
    }

    /**
     * Mengambil jumlah issue yang dibuat.
     *
     * @return JsonResponse
     */
    public function getIssuesCount()
    {
        $issuesCount = Issue::count();

        return response()->json([
            'success' => true,
            'issues_count' => $issuesCount,
        ], 200);
    }
}
