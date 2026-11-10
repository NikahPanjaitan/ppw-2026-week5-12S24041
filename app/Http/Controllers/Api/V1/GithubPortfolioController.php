<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GithubPortfolioController extends Controller
{
    public function show(string $username): JsonResponse
    {
        $client = Http::withoutVerifying()
            ->accept('application/vnd.github+json')
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->timeout(10);

        $userRes = $client->get("https://api.github.com/users/{$username}");

        if ($userRes->status() === 404) {
            return response()->json([
                'success' => false,
                'message' => "Akun GitHub '{$username}' tidak ditemukan.",
            ], 404);
        }

        if ($userRes->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal berkomunikasi dengan layanan eksternal GitHub API.',
            ], 502);
        }

        $reposRes = $client->get("https://api.github.com/users/{$username}/repos", [
            'sort' => 'updated',
            'per_page' => 3,
        ]);

        $profil = $userRes->json();
        $repos = collect($reposRes->json())->map(fn ($r) => [
            'repo_name' => $r['name'],
            'language'  => $r['language'],
            'stars'     => $r['stargazers_count'],
            'html_url'  => $r['html_url'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data portofolio koding mahasiswa berhasil diambil dari GitHub API.',
            'data' => [
                'github_login' => $profil['login'],
                'display_name' => $profil['name'] ?? $profil['login'],
                'bio'          => $profil['bio'],
                'public_repos' => $profil['public_repos'],
                'followers'    => $profil['followers'],
                'profile_url'  => $profil['html_url'],
                'latest_repos' => $repos,
            ],
        ], 200);
    }
}
