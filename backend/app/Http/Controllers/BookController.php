<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Katalog buku berhasil diambil.',
            'data' => $books
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'type' => 'required|in:novel,comic,anthology',
            'published_year' => 'required|integer|min:1800|max:'.(date('Y') + 1),
            'status' => 'required|in:unread,reading,completed',
        ]);

        $book = Book::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data buku baru berhasil ditambahkan.',
            'data' => $book
        ], 201);
    }

    public function show($id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data buku tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $book
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data buku tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'type' => 'required|in:novel,comic,anthology',
            'published_year' => 'required|integer|min:1800|max:'.(date('Y') + 1),
            'status' => 'required|in:unread,reading,completed',
        ]);

        $book->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data buku berhasil diperbarui.',
            'data' => $book
        ], 200);
    }

    public function destroy($id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data buku tidak ditemukan.'
            ], 404);
        }

        $book->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data buku berhasil dihapus dari sistem.'
        ], 200);
    }
}