<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Drivers\Gd\Driver;
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
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_dir', 'desc');
        $parentId = $request->get('parent_id'); // отримуємо з URL
        $allowedSorts = ['username', 'email', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        $comments = Cache::remember("comments_{$sortField}_{$sortDirection}_page_" . $request->get('page', 1), 60, function () use ($sortField, $sortDirection) {
            return Comment::with('children')
                ->whereNull('parent_id')
                ->orderBy($sortField, $sortDirection)
                ->paginate(25);
        });
        return view('comments.create', compact('comments','parentId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'regex:/^[a-zA-Z0-9]+$/u', 'max:255'],
            'email' => ['required', 'email'],
            'homepage' => ['nullable', 'url'],
            'text' => ['required'],
            'g-recaptcha-response' => 'required|captcha',
            'parent_id' => ['nullable', 'exists:comments,id'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,png,gif,txt,pdf', 'max:100'], // додано pdf
        ]);

        $text = strip_tags($request->text, '<a><code><i><strong>');

        $comment = Comment::create([
            'user_name' => $validated['username'],
            'email' => $validated['email'],
            'homepage' => $validated['homepage'] ?? null,
            'text' => $text,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        if ($request->hasFile('attachment')) {
            try {
                $file = $request->file('attachment');
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = Str::uuid() . '.' . $extension;
                $path = 'uploads/' . $filename;

                // Зберігаємо в залежності від типу
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $manager = ImageManager::gd(); // або ImageManager::imagick()
                    $image = $manager->read($file->getPathname());
                    $image->resize(320, 240, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save(public_path($path));
                    $type = 'image';
                } else {
                    $file->move(public_path('uploads'), $filename);
                    $type = in_array($extension, ['pdf']) ? 'pdf' : 'text';
                }

                // Поліморфне збереження
                $comment->media()->create([
                    'file_url' => '/' . $path,
                    'type' => $type,
                ]);
            } catch (\Exception $e) {
                Log::error('[Upload Error] ' . $e->getMessage());
                return response()->json(['error' => 'Не вдалося завантажити файл.'], 500);
            }
        }

        return response()->json(['success' => true, 'comment' => $comment]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = Comment::find($id);
        return view('comments.show', compact('comment'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        return view('comments.edit', compact('comment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'user_name' => 'required|string|max:100',
            'content' => 'required|string|max:1000',
            'attachment' => 'nullable|file|max:2048|mimes:jpg,jpeg,png,gif,pdf,docx,txt',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        $comment->user_name = $validated['user_name'];
        $comment->text = $validated['content'];

        if ($request->hasFile('attachment')) {
            try {
                // Видаляємо старий файл, якщо є
                $media = $comment->media()->first(); // отримуємо перший медіа-об'єкт
                if ($media) {
                    $oldFilePath = public_path($media->file_url);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                    $media->delete();
                }


                $file = $request->file('attachment');
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = Str::uuid() . '.' . $extension;
                $path = 'uploads/' . $filename;
                if ($extension === 'webp') {
                    throw new \Exception('WebP не підтримується на сервері');
                }
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
//                    $manager = new ImageManager(['driver' => 'gd']); // або 'imagick'
//                    $image = $manager->make($file->getPathname());
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($file->getPathname());
                    $image->resize(320, 240, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save(public_path($path));
                    $type = 'image';
                } else {
                    $file->move(public_path('uploads'), $filename);
                    $type = in_array($extension, ['pdf']) ? 'pdf' : 'text';
                }

                // Створюємо новий запис media
                $comment->media()->create([
                    'file_url' => '/' . $path,
                    'type' => $type,
                ]);
            } catch (\Exception $e) {
                Log::error('[Upload Error] ' . $e->getMessage());
                return response()->json(['error' => 'Не вдалося завантажити файл.'], 500);
            }
        }

        $comment->save();

//        return redirect()->route('comments.edit', $comment)->with('success', 'Коментар успішно оновлено!');
        return response()->json(['message' => 'Коментар успішно оновлено!']);

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return redirect()->route('comments.index')->with('success', 'Comment deleted');
    }
    public function listing(Request $request)
    {
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_dir', 'desc');

        $allowedSorts = ['user_name', 'email', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        $comments = Comment::with(['children', 'media'])->whereNull('parent_id')
            ->orderBy($sortField, $sortDirection)
            ->paginate(25);

        return view('comments.partials.comments-list', compact('comments'))->render(); // повертає HTML
    }

}
