<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use ApiResponse;

    public function profile(Request $request){

        $user=$request->user()->load('roles','permissions');
        return $this->success('User profile',
        ['user'=>new UserResource($user),
         'status'=>[
             'conversations_count'=>$user->conversations->count(),
             'unread_messages_count'=>$user->unreadMessagesCount(),
             'member_since'=>$user->created_at->diffForHumans(),]
        ]);
    }

    public function updateProfile(Request $request){

        $request->validate([
            'name'=>'sometimes|string|max:255',
            'username'=>'sometimes|string|max:255',
            'bio'=>'sometimes|string|max:500',
            'gender'=>'sometimes|in:male,female',
        ]);
        $user=$request->user();
        $user->update($request->only('name','username','bio','gender'));
        $user->update(['profile_completed'=>true]);

        return $this->success('Profile updated',new UserResource($user->load('roles','permissions')));

    }

    public function updateAvatar(Request $request){
        $request->validate([
            'avatar'=>'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $user=$request->user();
        if($user->avatar && Storage::exists($user->avatar)){
            Storage::delete($user->avatar);
        }

        $path=$request->file('avatar')->store('avatars', 'public');
        $avatarUrl = Storage::url($path);
        $user->update(['avatar'=>$avatarUrl]);
        $user->refresh();

        return $this->success('Avatar updated successfully',
            [
                'avatar_url'=>$avatarUrl,
                'avatar'=>$avatarUrl,
                'user'=>new UserResource($user->load('roles','permissions'))
            ]);
    }

    public function removeAvatar(Request $request)
    {
        $user=$request->user();
        if($user->avatar && Storage::exists($user->avatar)){
            Storage::delete($user->avatar);
        }
        $user->update(['avatar'=>null]);
        return $this->success('Avatar removed successfully');

    }

    public function changePassword(Request $request){
        $request->validate([
            'current_password'=>'required|string',
            'new_password'=>'required|string|min:8|confirmed',
        ]);

        $user=$request->user();

        if(!Hash::check($request->current_password,$user->password)){
            return $this->error('Current password is incorrect',400);
        }

        $user->update(['password'=>Hash::make($request->new_password)]);

        return $this->success('Password changed successfully');
    }

    public function conversations(Request $request){
        $user=$request->user();
        $conversations=$user->conversations()
            ->with(['lastMessage','users'])
            ->latest('last_message_at')
            ->paginate(10);

    return $this->success('Conversations retrieved successfully ',$conversations);
    }


    public function deleteAccount(Request $request){
        $request->validate([
            'password'=>'required|string',
            'confirm'=>'required|in:DELETE',
        ]);
        $user=$request->user();

        if(!Hash::check($request->password,$user->password)){
            return $this->error('Password is incorrect',400);
        }

        // Delete user data
        $user->tokens()->delete();
        $user->conversations()->detach();
        $user->messages()->delete();

        if($user->avatar && Storage::exists($user->avatar)){

            Storage::delete($user->avatar);
        }

        $user->delete();
        return $this->success('Account deleted successfully');

    }

    // Get all users for community page
    public function index(Request $request)
    {
        $currentUser = $request->user();
        $search = $request->query('search');
        $perPage = $request->query('per_page', 15);

        $query = User::where('id', '!=', $currentUser->id)
            ->whereNotNull('email_verified_at'); // Only show verified users

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->with('roles', 'permissions')
            ->paginate($perPage);

        // Add friendship status to each user
        $users->getCollection()->transform(function ($user) use ($currentUser) {
            $user->friendship_status = $currentUser->getFriendshipStatus($user->id);
            return $user;
        });

        return $this->success('Users retrieved successfully', $users);
    }

    // Get user profile by ID
    public function show(Request $request, $id)
    {
        $currentUser = $request->user();
        $user = User::with('roles', 'permissions')->findOrFail($id);

        // Add friendship status
        $user->friendship_status = $currentUser->getFriendshipStatus($user->id);
        $user->is_friend = $currentUser->isFriendWith($user->id);

        return $this->success('User profile retrieved', new UserResource($user));
    }



}
