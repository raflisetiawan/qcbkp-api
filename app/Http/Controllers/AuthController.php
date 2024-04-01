<?php

namespace App\Http\Controllers;

use App\Events\AssignUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Class AuthController
 *
 * This class handles the authentication related functionalities such as sign up, sign in, sign out, and retrieving user data with role.
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * This is a method docstring for the sign_up method.
     *
     * The sign_up method is responsible for handling the user registration process.
     * It validates the incoming request data, creates a new user record in the database,
     * assigns a default role to the user, and returns a JSON response with the registered user details.
     *
     * @param Request $request The incoming request object.
     * @return JsonResponse The JSON response containing the registered user details.
     */
    public function sign_up(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'approval_status' => false,
        ]);
        event(new AssignUserRole($user));

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
        ], 201);
    }

    /**
     * This method handles the sign in process for users.
     * It validates the email and password provided in the request,
     * checks if the email is registered in the database,
     * checks if the user's approval status is true,
     * and if the password matches the hashed password in the database.
     * If all conditions are met, it generates a token for the user and returns a JSON response with the user details and the token.
     * If any condition fails, it returns a JSON response with an appropriate error message.
     *
     * @param Request $request The incoming request object.
     * @return JsonResponse The JSON response containing the user details and token if successful, or an error message if failed.
     */

    public function sign_in(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response([
                'success' => false,
                'message' => 'Email tidak terdaftar'
            ], 404);
        }

        // Tambahkan pengecekan status persetujuan sebelum melakukan otentikasi
        if (!$user->approval_status) {
            return response([
                'success' => false,
                'message' => 'Akun Anda belum disetujui oleh admin. Mohon tunggu persetujuan.'
            ], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response([
                'success' => false,
                'message' => 'Email atau password yang Anda masukkan salah.'
            ], 401);
        }

        $token = $user->createToken('ApiToken')->plainTextToken;

        $response = [
            'success' => true,
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    /**
     * Logs out the authenticated user.
     *
     * This function logs out the currently authenticated user by calling the `logout` method of the `auth` helper function.
     * It then returns a JSON response with a success message.
     *
     * @return JsonResponse The JSON response with a success message.
     */
    public function sign_out()
    {
        auth()->logout();
        return response()->json([
            'success'    => true
        ], 200);
    }
    /**
     * Retrieves the user data along with their role.
     *
     * This method is responsible for retrieving the user data along with their role.
     * It first checks if the user is authenticated to access the user data with role.
     * If the user is not authenticated, it returns a JSON response with an "Unauthenticated" message.
     * If the user is authenticated, it retrieves the user's role and combines the user data and role data into a response.
     * The response includes the user's ID, name, email, phone number, role name, and email verification status.
     *
     * @param Request $request The incoming request object.
     * @return JsonResponse The JSON response containing the user data with role.
     */
    public function getUserWithRole(Request $request)
    {
        // Pastikan pengguna sudah diautentikasi untuk mengakses data pengguna dengan peran.
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Ambil peran pengguna
        $role = $user->role;

        // Anda dapat mengakses data peran melalui $role, misalnya:
        $roleName = $role->name; // Nama peran pengguna

        // Menggabungkan data pengguna dan peran dalam respons
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role' => $roleName, // Menambahkan nama peran
            'email_verified_at' => $user->email_verified_at,
            'image' => $user->image_path
        ];

        return response()->json([
            'user' => $userData,
        ], 200);
    }

    /**
 * Mengganti password pengguna.
 *
 * Metode ini memvalidasi permintaan dan kemudian mengubah password pengguna yang sesuai.
 *
 * @param Request $request Permintaan yang masuk.
 * @return JsonResponse JSON response yang berisi pesan sukses atau error.
 */
public function change_password(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:6',
        'confirm_password' => 'required|same:new_password',
    ], [
        'confirm_password.same' => 'Konfirmasi password harus sama dengan password baru.',
    ]);


    $user = $request->user();

    // Sementara memperlihatkan password yang di-hidden
    $user->makeVisible('password');

    if (!Hash::check($request->current_password, $user->password)) {
        // Mengembalikan password ke hidden
        $user->makeHidden('password');

        return response()->json([
            'success' => false,
            'message' => 'Password lama tidak sesuai.'
        ], 400);
    }

    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Password berhasil diubah.'
    ], 200);
}
}
