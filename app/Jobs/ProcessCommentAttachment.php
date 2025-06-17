<?php

namespace App\Jobs;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // або Imagick
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProcessCommentAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $comment;
    protected $filePath;

    public function __construct(Comment $comment, string $filePath)
    {
        $this->comment = $comment;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        try {
            $pathInfo = pathinfo($this->filePath);
            $extension = strtolower($pathInfo['extension']);
            $filename = Str::uuid() . '.' . $extension;
            $finalPath = 'uploads/' . $filename;

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($this->filePath);
                $image->resize(320, 240, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image->save(public_path($finalPath));
                $type = 'image';
            } else {
                copy($this->filePath, public_path($finalPath));
                $type = in_array($extension, ['pdf']) ? 'pdf' : 'text';
            }

            $this->comment->media()->create([
                'file_url' => '/' . $finalPath,
                'type' => $type,
            ]);

            // Прибрати тимчасовий файл
            unlink($this->filePath);

        } catch (\Exception $e) {
            Log::error('[ProcessCommentAttachment Error] ' . $e->getMessage());
        }
    }
}
