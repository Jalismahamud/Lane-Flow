<?php

namespace App\Http\Controllers\Api\Auth;


use Exception;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    use ApiResponse;

    public function profile()
    {
        try {

            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }

            $response = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
                'latitude' => $user->latitude ?? null,
                'longitude' => $user->longitude ?? null
            ];

            return $this->success($response, 'User profile retrieved successfully', 200);
        } catch (Exception $e) {

            Log::error('Quiz Profile Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['nullable', 'string', 'max:255'],
                'avatar' => ['nullable', 'image', 'max:20480'],
                'latitude' => ['nullable', 'string', 'max:255'],
                'longitude' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            $data = $validator->validated();

            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Helper::deleteAvatar($user->avatar);
                }
                $avatarPath = Helper::uploadImage($request->file('avatar'), 'profile');
                $data['avatar'] = $avatarPath;
            }

            $user->update($data);

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => url($user->avatar) ?? null,
                'latitude' => $user->latitude,
                'longitude' => $user->longitude
            ];

            return $this->success($userData, 'Profile updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Profile Update Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function updateAvatar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            if ($user->avatar) {
                Helper::deleteAvatar($user->avatar);
            }

            $avatarPath = Helper::uploadImage($request->file('avatar'), 'profile');

            $user->update(['avatar' => $avatarPath]);

            return $this->success(['avatar' => url($avatarPath)], 'Avatar updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Avatar Update Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'string', 'min:8'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 200);
            }

            $user = auth('api')->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->error([], 'current password is incorrect.', 200);
            }

            $user->update(['password' => Hash::make($request->password)]);

            return $this->success(['Password updated successfully'], 'Password updated successfully.', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function deleteProfile()
    {
        try {
            $user = auth('api')->user();

            if ($user->avatar) {
                Helper::deleteAvatar($user->avatar);
            }

            $user->delete();

            return $this->success([], 'Profile deleted successfully.', 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function updateLocation(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }


            $user = auth('api')->user();

            if (!$user) {
               return $this->error([], 'User not found.', 404);
            }

            $validatedData = $validator->validated();


            $user->update([
                'latitude' => $validatedData['latitude'],
                'longitude' => $validatedData['longitude'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated and broadcasted successfully.',
                'data' => [
                    'id' => $user->id,
                    'latitude' => (float)$user->latitude,
                    'longitude' => (float)$user->longitude
                ]
            ], 200);
        } catch (\Exception $e) {

            Log::error('Location Update API Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
