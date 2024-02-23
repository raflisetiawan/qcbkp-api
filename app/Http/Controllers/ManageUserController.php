<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManageUserController extends Controller
{

    /**
     * Retrieves all users with their roles.
     *
     * This method retrieves the users' data from the database, including their id, name, email, approval status, and role id.
     * It then converts the approval status to a boolean value and adds an approval status text based on the boolean value.
     * Additionally, it retrieves the role name for each user based on their role id.
     * Finally, it retrieves the id and name of all roles from the roles table.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the success status, users data, and roles data.
     */
    public function getAllUsersWithRoles()
    {
        // Mengambil data pengguna dengan kolom yang spesifik
        $users = User::select('id', 'name', 'email', 'approval_status', 'role_id')->get();

        // Mengonversi kolom approval_status menjadi boolean
        $users = $users->map(function ($user) {
            // Tambahkan atribut is_approved yang merupakan versi boolean dari approval_status
            $user->is_approved = (bool) $user->approval_status;
            // Tambahkan atribut approval_status_text berdasarkan is_approved
            $user->approval_status_text = $user->is_approved ? 'Di-setujui' : 'Tidak Di-setujui';
            // Tambahkan atribut role_name dan role_id berdasarkan role
            $user->role_id = $user->role_id;
            $user->role_name = $this->getRoleNameById($user->role_id);
            return $user;
        });

        // Mendapatkan hanya 'name' dan 'id' dari semua data peran dari tabel roles
        $roles = Role::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'users' => $users,
            'roles' => $roles,
        ], 200);
    }

    /**
     * Mendapatkan nama peran berdasarkan ID peran.
     *
     * @param int $roleId
     * @return string|null
     */
    private function getRoleNameById($roleId)
    {
        // Gantilah ini dengan logika untuk mendapatkan nama peran berdasarkan ID peran.
        // Contoh: return Role::where('id', $roleId)->value('name');
        $role = Role::find($roleId);

        // Pastikan role ditemukan sebelum mencoba mengakses namanya
        return $role ? $role->name : null;
    }



    /**
     * Mengganti peran pengguna berdasarkan ID pengguna.
     *
     * @param int $userId
     * @param int $newRoleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeUserRole(Request $request)
    {
        // Mencari pengguna berdasarkan ID
        $user = User::find($request->userId);

        // Memastikan pengguna ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        // Mencari peran baru berdasarkan ID
        $newRole = Role::find($request->newRoleId);

        // Memastikan peran baru ditemukan
        if (!$newRole) {
            return response()->json([
                'success' => false,
                'message' => 'Peran baru tidak ditemukan.',
            ], 404);
        }

        // Mengganti peran pengguna
        $user->role_id = $request->newRoleId;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Peran pengguna berhasil diubah.',
            'user' => $user,
        ], 200);
    }

    /**
     * Mengganti status persetujuan pengguna berdasarkan ID pengguna.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleApprovalStatus(Request $request)
    {
        // Mencari pengguna berdasarkan ID
        $user = User::find($request->userId);

        // Memastikan pengguna ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        // Toggle nilai approval_status
        $user->approval_status = !$user->approval_status;
        $user->save();

        // Mendapatkan teks status persetujuan yang baru
        $approvalStatusText = $user->approval_status ? 'Di-setujui' : 'Tidak Di-setujui';

        return response()->json([
            'success' => true,
            'message' => 'Status persetujuan pengguna berhasil diubah.',
            'user' => $user,
            'approval_status_text' => $approvalStatusText,
        ], 200);
    }
    /**
     * Mengedit profil pengguna berdasarkan ID pengguna.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editUserProfile(Request $request)
    {
        // Validasi data yang diterima dari request
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $request->user_id,
            'image_path' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Mencari pengguna berdasarkan ID
        $user = User::find($request->user_id);

        // Memastikan pengguna ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }


        // Memperbarui data pengguna
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        // Memeriksa dan memperbarui gambar profil jika ada
        if ($request->hasFile('image_path')) {
            if ($user->image_path) {
                Storage::delete('public/users/' . basename($user->image_path));
            }
            $imagePath = $request->file('image')->store('users', 'public');
            $user->image_path = $imagePath;
        }

        // Menyimpan perubahan
        $user->save();

        // Mengembalikan respons JSON berhasil
        return response()->json([
            'success' => true,
            'message' => 'Profil pengguna berhasil diperbarui.',
            'user' => $user,
        ], 200);
    }

    /**
     * Mengambil jumlah pengguna yang mendaftar.
     *
     * @return JsonResponse
     */
    public function getUsersCount()
    {
        $usersCount = User::count();

        return response()->json([
            'success' => true,
            'users_count' => $usersCount,
        ], 200);
    }
}
