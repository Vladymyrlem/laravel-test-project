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
        Storage::disk('public')->makeDirectory('uploads');

        $imageFiles = glob(database_path('seed_files/*.jpg'));
        $textFiles = glob(database_path('seed_files/*.txt'));

        Comment::factory()
            ->count(100)
            ->create()
            ->each(function ($comment, $index) use ($imageFiles, $textFiles) {
                // Встановлюємо випадкову дату в межах останніх 3 днів
                $randomDate = Carbon::now()->subDays(rand(0, 2))->subMinutes(rand(0, 1440));
                $comment->created_at = $randomDate;
                $comment->updated_at = $randomDate;
                $comment->save();

                $this->attachMedia($comment, $imageFiles, $textFiles);

                if ($index < 10) {
                    Comment::factory()
                        ->count(rand(5, 10))
                        ->create(['parent_id' => $comment->id])
                        ->each(function ($reply) use ($imageFiles, $textFiles) {
                            $randomDate = Carbon::now()->subDays(rand(0, 2))->subMinutes(rand(0, 1440));
                            $reply->created_at = $randomDate;
                            $reply->updated_at = $randomDate;
                            $reply->save();

                            $this->attachMedia($reply, $imageFiles, $textFiles);
                        });
                }
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
