<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Request\Auth\TagLoginRequest;
use App\DTOs\Response\AuthResponse;
use App\DTOs\Response\VeterinarianResponse;
use App\DTOs\Response\WorkstationResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workstation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        return $this->loginWithTag(TagLoginRequest::createFrom($request));
    }

    public function loginWithTag(TagLoginRequest $request): JsonResponse
    {
        $throttleKey = $request->workstation.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'workstation' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }

        $workstation = Workstation::where('hash', $request->workstation)->first();

        if (!$workstation) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'workstation' => ['Estação de trabalho não reconhecida.'],
            ]);
        }

        $hashedTag = hash('sha256', $request->tag . config('app.tag_salt'));

        $user = User::where('tag_hash', $hashedTag)
            ->where('is_veterinarian', true)
            ->first();

        if (!$user) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'tag' => ['Veterinário não encontrado ou tag inválida.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $tokenResult = $user->createToken('auth_token');

        $tokenResult->accessToken->forceFill([
            'workstation_id' => $workstation->id,
        ])->save();

        $response = new AuthResponse(
            access_token: $tokenResult->plainTextToken,
            token_type: 'Bearer',
            user: VeterinarianResponse::fromModel($user),
            workstation: WorkstationResponse::fromModel($workstation),
        );

        return response()->json($response->toArray());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revogado com sucesso.']);
    }
}
