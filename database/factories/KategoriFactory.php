<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kategori>
 */
class KategoriFactory extends Factory
{
    protected $model = Kategori::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_kategori' => 'KTG-' . fake()->unique()->numerify('###'),
            'nama_kategori' => fake()->randomElement([
                'Rekayasa Perangkat Lunak',
                'Sistem Informasi',
                'Kecerdasan Buatan & Data Science',
                'Jaringan Komputer & Keamanan Siber',
                'Matematika Diskrit & Sains',
                'Struktur Data & Algoritma',
                'Basis Data & Komputasi Awan',
            ]),
        ];
    }
}
