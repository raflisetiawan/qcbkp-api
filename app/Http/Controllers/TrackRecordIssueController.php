<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QualityIssue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            'quality_issues.discovery_file',
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
        $issue = QualityIssue::select('id', 'closed', 'closed_date', 'todos', 'quality_control_verification', 'discovery_file')
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
            'closed' => 'required|string',
            'closed_date' => 'nullable|date',
            'todos' => 'nullable|string',
            'quality_control_verification' => 'nullable|string',
            'discovery_file' => 'nullable|file|mimes:xls,xlsx'
        ]);

        $closed = $request->closed === 'true' ? true : false;

        // Cari isu berdasarkan ID
        $issue = QualityIssue::findOrFail($id);

          // Jika ada file discovery yang diunggah, simpan file tersebut
          if ($request->hasFile('discovery_file')) {
            // Hapus gambar lama jika ada
            if ($issue->discovery_file) {
                Storage::delete('public/discovery_files/' . $issue->discovery_file);
            }

            // Ambil file yang diunggah
            $file = $request->file('discovery_file');

            // Mengganti spasi dalam nama file dengan karakter underscore
            $fileName = Str::of($file->getClientOriginalName())->replace(' ', '_');

            // Simpan file ke direktori yang ditentukan dengan nama baru
            $file->storeAs('public/discovery_files', $fileName);

            // Simpan nama file ke dalam data isu
            $issue->discovery_file = $fileName;
        }



        // Update isu dengan data baru
        $issue->update([
            'closed' => $closed, // Gunakan nilai boolean yang sudah diubah
            'closed_date' => $request->closed_date,
            'todos' => $request->todos,
            'quality_control_verification' => $request->quality_control_verification,
        ]);


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

   /**
     * Retrieve excel file from URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getExcelFromUrl(Request $request)
    {
        try {
            // Ambil id QualityIssue dari request
            $id = $request->id;

            // Ambil file discovery_file dari QualityIssue berdasarkan id
            $qualityIssue = QualityIssue::findOrFail($id);
            $discoveryFile = $qualityIssue->discovery_file;

            // Jika file discovery_file ada
            if ($discoveryFile) {
                // Buat path lengkap ke file discovery
                $filePath = public_path("storage/discovery_files/{$discoveryFile}");
                $excelData = file_get_contents($filePath);
                $base64Excel = base64_encode($excelData);

                if (file_exists($filePath)) {
                    // Load file Excel menggunakan PHPExcel
                    $spreadsheet = IOFactory::load($filePath);

                    // Ubah ke format PDF
                    $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
                    $writer->writeAllSheets();
                    $writer->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                    // Simpan PDF ke dalam string
                    ob_start();
                    $writer->save('php://output');
                    $pdfContent = ob_get_clean();

                    // Konversi ke base64
                    $base64Pdf = base64_encode($pdfContent);

                    // Kirim response API dengan file PDF dalam bentuk base64
                    return response()->json([
                        'success' => true,
                        'pdf_base64' => $base64Pdf,
                        'excel_base64' => $base64Excel,
                    ]);
                } else {
                    // Jika file discovery_file tidak ditemukan, kirim respons error
                    throw new \Exception('Discovery file not found.');
                }
            } else {
                // Jika file discovery_file tidak ada, kirim respons error
                throw new \Exception('Discovery file not found.');
            }
        } catch (\Exception $e) {
            // Tangani kesalahan dan kirim respons error
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
