<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {

        $users = User::latest()->get()->map(function ($user) {
            return [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->roles->pluck('name')->toArray(),
                'permission' => [],
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'User retrieved successfully'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // Tạo validator thay vì dùng $request->validate()
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'required|digits:10|regex:/^[0-9]{10}$/|unique:users,phone',
        ], [
            'name.required' => "Tên không được để trống",
            'email.required' => "Email không được để trống",
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.digits' => 'Số điện thoại phải có đúng 10 số.',
            'phone.regex' => 'Số điện thoại chỉ được chứa số từ 0-9.',
            'phone.unique' => 'Số điện thoại này đã tồn tại.',
            'password.required' => 'Mật khẩu không được để trống.',
        ]);

        // Nếu có lỗi validation, trả về response tùy chỉnh
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Lấy dữ liệu đã validated
        $validatedData = $validator->validated();

        // Tạo mã SKU tự động
        $validatedData['sku'] = 'TRU-' . strtoupper(Str::random(8));

        // Tạo user
        $user = User::create($validatedData);

        // Trả về response JSON chuyên nghiệp
        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Tạo thành công người dùng',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User not found',
                ],
                404
            );
        }
        return response()->json(
            [
                'success' => true,
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->roles->pluck('name')->toArray(), // Lấy danh sách tên role
                    'permission' => []
                ],
                'message' => 'User retrieved successfully',
            ],
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Kiểm tra user có tồn tại không
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Tạo validator
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|min:6',
            'phone' => 'sometimes|digits:10|regex:/^[0-9]{10}$/|unique:users,phone,' . $id,
        ], [
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.digits' => 'Số điện thoại phải có đúng 10 số.',
            'phone.regex' => 'Số điện thoại chỉ được chứa số từ 0-9.',
            'phone.unique' => 'Số điện thoại này đã tồn tại.',
        ]);

        // Nếu có lỗi validation, trả về response tùy chỉnh
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Lấy dữ liệu hợp lệ
        $validatedData = $validator->validated();

        // Cập nhật user
        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Cập nhật người dùng thành công',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User not found',
                ],
                404
            );
        }
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'Xóa người dùng thành công'
        ]);
    }
}