<?php

namespace App\Services\Resource;

use App\Models\Link;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Parser
{
    private Link $link;
    private int $page;
    protected $onProgress;

    public function __construct(
        Link $link,
        int $page = 1
    ) {
        $this->page = $page;
        $this->link = $link;
    }


    // Функція для виводу інформації в консолі
    public function onProgress(
        callable $callback
    ): self {
        $this->onProgress = $callback;
        return $this;
    }


    protected function report(
        string $msg
    ): void {
        if ($this->onProgress) {
            call_user_func($this->onProgress, $msg);
        }
    }

    public function run(): void
    {
        $html = new Html($this->parseHtml());

        $comments = $html->getComments();

        foreach ($comments as $comment) {
            $imgName = false;
            if (!empty($comment->user->img)) {
                $imgName = $comment->user->resource_user_id . '.png';
                Storage::put('/images/' . $imgName, file_get_contents($comment->user->img));
            }

            $user = User::query()
            ->updateOrCreate(
                [
                    'resource_user_id' => $comment->user->resource_user_id
                ],
                collect($comment->user)
                    ->only([
                        'name',
                        'country_code',
                        'comments_count'
                    ])
                    ->put('img', $imgName ? $imgName : null)
                    ->toArray()
            );

            Comment::query()
                ->updateOrCreate(
                    [
                        'link_id' => $this->link->id,
                        'user_id' => $user->id,
                        'date_added' => $comment->date_added
                    ],
                    collect($comment)
                        ->only([
                            'rate',
                            'title',
                            'text',
                            'date_exp'
                        ])
                        ->toArray()
                );
        }

        $this->report('Опрацьовано ' . count($comments) . ' коментарів');


        $nextPage = $html->getNextPage();
        if ($nextPage) {
            $this->report('Пауза 3сек.');
            sleep(3);
            $page = preg_replace('/(.*?)\?page=(\d+)$/', '$2', $nextPage);

            if ($page > 1) {
                $this->report('-----------------------');
                $this->report('### Сторінка №' . $page);
            }

            (new self($this->link, $page))
                ->onProgress(fn($msg) => $this->report($msg))
                ->run();
        }
    }

    private function getUrl(): string
    {
        return $this->link->url . ($this->page > 1 ? '?page=' . $this->page : '');
    }

    private function parseHtml(): string|false
    {
        return file_get_contents(
            $this->getUrl(),
            false,
            stream_context_create(
                [
                    "http" => [
                        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) " .
                                    "AppleWebKit/537.36 (KHTML, like Gecko) " .
                                    "Chrome/120.0.0.0 Safari/537.36\r\n"
                    ]
                ]
            )
        );
    }
}
