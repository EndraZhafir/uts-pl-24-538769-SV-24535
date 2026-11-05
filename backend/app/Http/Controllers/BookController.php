<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'type' => 'required|in:novel,comic,anthology',
            'published_year' => 'required|integer|min:1800|max:'.(date('Y') + 1),
            'status' => 'required|in:unread,reading,completed',
        ]);

        if ($validator->fails()) {
            Log::warning('Book creation validation failed', [
                'actor_id' => $request->user()?->id ?? 'unauthenticated',
                'actor_name' => $request->user()?->name ?? 'guest',
                'client_ip' => $request->ip(),
                'action' => 'create_book_validation',
                'failed_fields' => array_keys($validator->errors()->toArray()),
                'errors' => $validator->errors()->toArray(),
                'reason' => 'Payload masukan pengguna melanggar aturan skema validasi katalog buku',
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validasi data buku gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $book = Book::create($validated);

        Log::info('Book successfully created and stored', [
            'actor_id' => $request->user()?->id ?? 'system',
            'actor_name' => $request->user()?->name ?? 'system',
            'client_ip' => $request->ip(),
            'action' => 'create_book',
            'book_id' => $book->id,
            'book_title' => $book->title,
            'book_type' => $book->type,
            'reason' => 'Pengguna mengirimkan payload valid dan data berhasil dicatat ke basis data',
        ]);

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