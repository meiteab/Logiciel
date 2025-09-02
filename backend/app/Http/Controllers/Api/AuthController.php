<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Connexion de l'utilisateur et création du token.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Vérifier si l'utilisateur est actif
        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Le compte n\'est pas actif'
            ], 403);
        }

        // Vérifier si l'utilisateur est verrouillé
        if ($user->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Le compte est temporairement verrouillé'
            ], 423);
        }

        // Mettre à jour la dernière connexion
        $user->update([
            'derniere_connexion' => now(),
            'tentatives_connexion' => 0
        ]);

        // Créer le token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'statut' => $user->statut,
                    'profil' => $user->profil ? [
                        'id' => $user->profil->id,
                        'nom' => $user->profil->nom,
                        'code' => $user->profil->code
                    ] : null,
                    'personnel' => $user->personnel ? [
                        'id' => $user->personnel->id,
                        'matricule' => $user->personnel->matricule,
                        'prenom' => $user->personnel->prenom,
                        'nom_famille' => $user->personnel->nom_famille
                    ] : null
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Déconnexion de l'utilisateur (révoquer le token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Actualiser le token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Révoquer le token actuel
        $request->user()->currentAccessToken()->delete();
        
        // Créer un nouveau token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token actualisé avec succès',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Obtenir les informations de l'utilisateur authentifié.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profil', 'personnel']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'statut' => $user->statut,
                    'derniere_connexion' => $user->derniere_connexion,
                    'two_factor_enabled' => $user->two_factor_enabled,
                    'profil' => $user->profil ? [
                        'id' => $user->profil->id,
                        'nom' => $user->profil->nom,
                        'code' => $user->profil->code,
                        'description' => $user->profil->description,
                        'permissions_specifiques' => $user->profil->permissions_specifiques
                    ] : null,
                    'personnel' => $user->personnel ? [
                        'id' => $user->personnel->id,
                        'matricule' => $user->personnel->matricule,
                        'prenom' => $user->personnel->prenom,
                        'nom_famille' => $user->personnel->nom_famille,
                        'civilite' => $user->personnel->civilite,
                        'date_naissance' => $user->personnel->date_naissance,
                        'telephone' => $user->personnel->telephone,
                        'email_professionnel' => $user->personnel->email_professionnel,
                        'fonction' => $user->personnel->fonction,
                        'date_embauche' => $user->personnel->date_embauche,
                        'statut' => $user->personnel->statut
                    ] : null
                ]
            ]
        ]);
    }

    /**
     * Changer le mot de passe.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe actuel est incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe changé avec succès'
        ]);
    }
}
