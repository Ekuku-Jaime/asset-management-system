<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ActivationController extends Controller
{
    /**
     * Mostrar formulário de ativação
     */
    public function showActivationForm($token)
    {
        // Buscar usuário pelo token
        $user = User::where('activation_token', $token)
                    ->where('active', false)
                    ->first();
        
        // Verificar se o token é válido e não expirou (48 horas)
        if (!$user) {
            return view('auth.activate', [
                'user' => null,
                'token' => $token
            ]);
        }
        
        // Verificar se o token expirou (48 horas)
        $tokenAge = $user->created_at->diffInHours(now());
        if ($tokenAge > 48) {
            return view('auth.activate', [
                'user' => null,
                'token' => $token
            ]);
        }
        
        return view('auth.activate', [
            'user' => $user,
            'token' => $token
        ]);
    }
    
    /**
     * Processar ativação da conta
     */
    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
        ], [
            'password.required' => 'A password é obrigatória.',
            'password.min' => 'A password deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'As passwords não coincidem.',
            'password.regex' => 'A password deve conter pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial.',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Buscar usuário
        $user = User::where('activation_token', $request->token)
                    ->where('active', false)
                    ->first();
        
        if (!$user) {
            return redirect()->route('activation.show', ['token' => $request->token])
                ->with('error', 'Token de ativação inválido ou expirado.');
        }
        
        // Verificar se o token expirou
        $tokenAge = $user->created_at->diffInHours(now());
        if ($tokenAge > 48) {
            return redirect()->route('activation.show', ['token' => $request->token])
                ->with('error', 'O link de ativação expirou. Por favor, solicite um novo.');
        }
        
        try {
            // Atualizar usuário
            $user->update([
                'password' => Hash::make($request->password),
                'active' => true,
                'activation_token' => null,
                'email_verified_at' => now(),
            ]);
            
            // Login automático
            auth()->login($user);
            
            // Redirecionar para dashboard com mensagem de sucesso
            return redirect()->route('dashboard')
                ->with('success', 'Conta ativada com sucesso! Bem-vindo ao sistema.');
                
        } catch (\Exception $e) {
            return redirect()->route('activation.show', ['token' => $request->token])
                ->with('error', 'Ocorreu um erro ao ativar a conta. Por favor, tente novamente.');
        }
    }
    
    /**
     * Página de sucesso
     */
    public function success()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        return view('auth.activation-success');
    }
}