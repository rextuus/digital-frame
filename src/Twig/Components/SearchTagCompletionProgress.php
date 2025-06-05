<?php

namespace App\Twig\Components;

use App\Entity\SearchTag;
use App\Repository\SearchTagRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class SearchTagCompletionProgress
{
    use DefaultActionTrait;

    #[LiveProp]
    public SearchTag $searchTag;

    #[LiveProp]
    public int $searchTagId = 1;

    public function __construct(private readonly SearchTagRepository $searchTagRepository)
    {
    }


    #[LiveAction]
    public function refresh(): void
    {
        $this->searchTag = $this->searchTagRepository->find($this->searchTagId);
    }

    public function getCurrentPageCleaned(): int
    {
        if ($this->searchTag->getCurrentPage() === 1){
            return 0;
        }

        return $this->searchTag->getCurrentPage();
    }
}
