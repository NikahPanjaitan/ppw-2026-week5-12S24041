<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Buku>
 */
class BukuFactory extends Factory
{
    protected $model = Buku::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'isbn' => fake()->unique()->numerify('978-602-###-###-#'),
            'judul' => fake()->sentence(fake()->numberBetween(3, 6)),
            'penulis' => fake()->name(),
            'penerbit' => fake()->company(),
            'tahun_terbit' => fake()->numberBetween(2015, 2024),
            'kategori_id' => Kategori::factory(),
            'stok' => fake()->numberBetween(1, 30),
            'sinopsis' => fake()->paragraph(3),
        ];
    }
}
