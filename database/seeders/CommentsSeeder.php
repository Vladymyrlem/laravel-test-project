<?php

namespace Database\Seeders;

use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        // Очищення
        Comment::truncate();
        Storage::disk('public')->deleteDirectory('uploads');
        Storage::disk('public')->makeDirectory('uploads');

        $imageFiles = glob(database_path('seed_files/*.jpg'));
        $textFiles = glob(database_path('seed_files/*.txt'));

        $rootComments = Comment::factory()
            ->count(100)
            ->create()
            ->each(function ($comment) use ($imageFiles, $textFiles) {
                $this->attachMedia($comment, $imageFiles, $textFiles);
            });

        Comment::factory()
            ->count(173)
            ->create()
            ->each(function ($comment) use ($rootComments, $imageFiles, $textFiles) {
                $comment->parent_id = $rootComments->random()->id;
                $comment->save();
                $this->attachMedia($comment, $imageFiles, $textFiles);
            });
    }

    protected function attachMedia(Comment $comment, $imageFiles, $textFiles): void
    {
        // Вибір випадкового файлу
        $filePath = rand(0, 1)
            ? $imageFiles[array_rand($imageFiles)]
            : $textFiles[array_rand($textFiles)];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $filename = Str::uuid() . '.' . $extension;
        $relativePath = 'uploads/' . $filename;
        $destinationPath = public_path($relativePath);

        // Створити папку, якщо її ще не існує
        if (!file_exists(dirname($destinationPath))) {
            mkdir(dirname($destinationPath), 0777, true);
        }

        // Скопіювати файл у public/uploads/
        copy($filePath, $destinationPath);

        // Зберегти запис у media
        $comment->media()->create([
            'file_url' => $relativePath, // без "storage/"
            'type' => $this->detectType($extension),
        ]);
    }


    protected function detectType($extension): string
    {
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'text';
    }
}
