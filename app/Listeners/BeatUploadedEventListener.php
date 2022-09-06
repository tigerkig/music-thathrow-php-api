<?php

namespace App\Listeners;

use App\Events\BeatUploadedEvent;
use App\Models\Beat;
use App\Notifications\BeatProcessedNotification;
use FFMpeg\Filters\Audio\AudioFilters;
use FFMpeg\Format\Audio\Mp3;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as FFMpeg2;

class BeatUploadedEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function __construct()
    {
        //
    }

    public function handle(BeatUploadedEvent $event)
    {
        $beat = Beat::find($event->beat->id) ;
        Log::info('Converting beat', [
            'beat' => $beat
        ]);
        $tagger=storage_path('app/tagging/tagger.wav');
        $outputFile = 'tagging/beats/' . $beat->id . '/' . $beat->original->name . '.mp3';
        $basePath = sprintf("users/%d/beats/%d", $beat->user_id, $beat->id);


        $sourceUrl = Storage::temporaryUrl($beat->original->url,  now()->addMinutes(5));
        FFMpeg2::openUrl(
            $sourceUrl, []
        )
            ->addFilter(function (AudioFilters $filters) use ($tagger) {
                $filters->custom("amovie='{$tagger}':loop=0,asetpts=N/SR/TB[beep];[0][beep]amix=duration=shortest,volume=2");
            })->export()
            ->inFormat(new Mp3())
            ->toDisk('local')
            ->save($outputFile);


        $preview = Storage::putFile(
            'public/' . $basePath,
            storage_path('app/' . $outputFile),
            ['visibility' => 'public']
        );

        $beat->preview()->create([
            'public' => true,
            'name' => $beat->original->name,
            'file_size' => Storage::disk('local')->size($outputFile),
            'file_type' => $beat->original->file_type,
            'type' => 'PREVIEW',
            'url' => $preview
        ]);

        if ($beat->is_free) {
            $beat->download()->create([
                'public' => false,
                'name' => $beat->original->name,
                'file_size' => Storage::disk('local')->size($outputFile),
                'file_type' => $beat->original->file_type,
                'type' => 'DOWNLOAD',
                'url' => $preview
            ]);
        }

        $beat->status = 3;
        $beat->save();

        FFMpeg2::cleanupTemporaryFiles();
        $beat->creator->notify((new BeatProcessedNotification($beat))->delay(now()->addMinutes(10)));
    }
}
