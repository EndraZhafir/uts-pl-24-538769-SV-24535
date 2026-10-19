<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User default untuk pengujian & login frontend
        User::firstOrCreate(
            ['email' => 'admin@mybook.test'],
            [
                'name' => 'Endra Zhafir',
                'password' => Hash::make('password123'),
            ]
        );

        // Data awal katalog buku & komik
        $initialBooks = [
            [
                'title' => 'One Piece: Romance Dawn',
                'author' => 'Eiichiro Oda',
                'type' => 'comic',
                'published_year' => 1997,
                'status' => 'reading',
            ],
            [
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'type' => 'novel',
                'published_year' => 1980,
                'status' => 'completed',
            ],
            [
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'type' => 'anthology',
                'published_year' => 2008,
                'status' => 'reading',
            ],
        ];

        foreach ($initialBooks as $book) {
            Book::firstOrCreate(['title' => $book['title']], $book);
        }
    }
}