<?php

namespace App\Components;

use App\Repository\FileRepository;
use App\Repository\PostRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent('search')]
class SearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public string $type;

    #[LiveProp]
    public int $max;

    #[LiveProp]
    public bool $granted = false;

    public function __construct(private PostRepository $postRepository, private FileRepository $fileRepository)
    {
    }

    public function getResults(): array
    {
        if (empty($this->query) || strlen($this->query) < 2) {
            return [];
        }

        if ($this->type === 'posts') {
            return $this->postRepository->search($this->query, $this->max);
        }

        if ($this->type === 'files') {
            return $this->fileRepository->search($this->query, $this->max, $this->granted);
        }

        return [];
    }
}
