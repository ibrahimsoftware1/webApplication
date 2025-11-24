<?php

namespace App\Http\Controllers\Api;

use App\Events\OnlineStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\chatting\Conversation;
use App\Traits\ApiResponse;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'avatar' => $request->avatar ? $request->file('avatar')->store('avatars', 'public') : null,
        ]);

        // Ensure 'user' role exists, create if it doesn't
        $userRole = Role::firstOrCreate(['name' => 'user']);
        
        // Ensure 'create conversations' permission exists, create if it doesn't
        $createConversationPermission = Permission::firstOrCreate(['name' => 'create conversations']);

        // Assign role and permission to user
        $user->assignRole($userRole);
        $user->givePermissionTo($createConversationPermission);

        event(new Registered($user));

        return $this->ok('Registered successfully, Please verify your email address');
    }

    public function login(LoginRequest $request){

            if(!Auth::attempt($request->only('email','password'))){
                return $this->error('Invalid credentials',401);
            }
            $user=User::where('email',$request->email)->first();

            if(!$user->hasVerifiedEmail()){
                Auth::logout();
                return $this->error('Please verify your email address',
                    ['email_verified'=>false],403);
            }

            $token=$user->createToken('auth_token')->plainTextToken;
            $user->markAsOnline();
            broadcast(new OnlineStatusChanged($user,true))->toOthers();

            // Ensure user is in community chat
            $this->addUserToCommunityChat($user);

            return $this->success('Logged in successfully',
                [
                    'user'=>new UserResource($user->load('roles','permissions')),
                    'access_token'=>$token,

                ]);

        }

        //Email verification (API)
    public function verifyEmail(Request $request ,$id , $hash){

        $user=User::findOrFail($id);

        if(!hash_equals($hash,sha1($user->getEmailForVerification()))){
            return $this->error('Invalid verification Link',400);
        }
        if($user->hasVerifiedEmail()){
            return $this->error('Email already verified',400);
        }
        if($user->markEmailAsVerified()){
            event(new Verified($user));
            
            // Add user to community chat when email is verified
            $this->addUserToCommunityChat($user);
        }
        return $this->success('Email verified successfully',
        ['verified'=>true]);
        }

    // Add user to community chat
    private function addUserToCommunityChat(User $user)
    {
        // Find or create the community chat
        $communityChat = Conversation::where('name', 'Community Chat')
            ->where('type', 'group')
            ->first();

        if (!$communityChat) {
            // Create community chat if it doesn't exist
            $communityChat = Conversation::create([
                'type' => 'group',
                'name' => 'Community Chat',
                'description' => 'General community chat for everyone',
                'created_by' => $user->id,
            ]);
        }

        // Add user to community chat if not already in it
        if (!$user->isInConversation($communityChat->id)) {
            $communityChat->addParticipants([$user->id], false);
        }
    }

        //Email verification (Web - redirects to frontend)
    public function verifyEmailWeb(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Use APP_URL from config (respects .env file)
        $baseUrl = config('app.url', 'http://localhost');

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect($baseUrl . '/chat-fixed.html?verify_error=1&message=' . urlencode('Invalid verification link'));
        }

        if ($user->hasVerifiedEmail()) {
            return redirect($baseUrl . '/chat-fixed.html?verify_error=1&message=' . urlencode('Email already verified'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            
            // Add user to community chat when email is verified
            $this->addUserToCommunityChat($user);
        }

        // Redirect to frontend with success message
        return redirect($baseUrl . '/chat-fixed.html?verify_success=1&id=' . $id . '&hash=' . $hash);
    }


        //Resend verification email

    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $user=User::where('email',$request->email)->first();

        if($user->hasVerifiedEmail()){
            return $this->error('Email already verified',400);
        }

        $user->sendEmailVerificationNotification();
        return $this->success('Verification email resent successfully');

    }
    //forget Password
    public function forgotPassword(Request $request){
        $request->validate([
            'email'=>'required|email|exists:users,email',
        ]);

        $status=Password::sendResetLink($request->only('email'));
        if($status===Password::RESET_LINK_SENT){
            return $this->success('Password reset link sent Successfully');
        }
        return $this->error('Invalid email address',400);
    }

    //Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success('Password reset successfully');
        }

        return $this->error('Invalid token or expired link', 400);
    }

    //Logout

    public function Logout(Request $request){
        $user=User::where('is_online',true);

        $request->user()->markAsOffline();
       // broadcast(new OnlineStatusChanged($user, false));

        $request->user()->currentAccessToken()->delete();
        return $this->success('Logged out successfully');
    }

}

