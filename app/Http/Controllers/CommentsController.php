<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_dir', 'desc');

        // Враховуємо назви полів у БД
        $allowedSorts = ['user_name', 'email', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        $page = $request->get('page', 1);
        $cacheKey = "comments_{$sortField}_{$sortDirection}_page_{$page}";

        $comments = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($sortField, $sortDirection) {
            return Comment::with('children')
                ->whereNull('parent_id')
                ->orderBy($sortField, $sortDirection)
                ->paginate(25);
        });

        return view('comments.index', compact('comments', 'sortField', 'sortDirection'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {


    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {

    }
}
