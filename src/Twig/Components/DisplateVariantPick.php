<?php

namespace App\Twig\Components;

use App\Entity\DisplateImage;
use App\Entity\SearchTag;
use App\Form\DisplateTagData;
use App\Form\DisplateTagType;
use App\Repository\SearchTagRepository;
use App\Service\Displate\ImageDto;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Unsplash\TagVariant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DisplateVariantPick extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public string $variant;

    #[LiveProp(writable: true)]
    public ?DisplateTagData $initialFormData = null;

    #[LiveProp(writable: true)]
    public ?ImageDto $image = null;
    #[LiveProp(writable: true)]
    public string $searchTerm = '';

    public function __construct(
        private readonly SearchTagRepository $searchTagRepository,
        private readonly FrameConfigurationService $frameConfigurationService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            DisplateTagType::class,
            $this->initialFormData,
            ['tags' => $this->searchTagRepository->getExistingDisplateTags(), 'searchTerm' => $this->searchTerm]
        );
    }

    #[LiveAction]
    public function submit(): RedirectResponse
    {
        $this->submitForm();

        /** @var DisplateTagData $data */
        $data = $this->getForm()->getData();

        if ($data->getExistingTag() !== null) {
            return $this->displayImage($data->getExistingTag());
        }

        // check if tag may already exists
        $existingTag = $this->searchTagRepository->findOneBy(['term' => $data->getNewTag()]);
        if ($existingTag === null){
            $existingTag = $data->getNewTag();
        }

        return $this->displayImage($existingTag);
    }

    private function displayImage(SearchTag|string $tag): RedirectResponse
    {
        // handle the tag
        if (!$tag instanceof SearchTag) {
            $newSearchTag = new SearchTag();
            $newSearchTag->setTerm($tag);
            $newSearchTag->setCurrentPage(0);
            $newSearchTag->setFullyLStored(false);
            $newSearchTag->setTotalPages($this->image->getTotalPagesForSearchTag());
            $newSearchTag->setVariant(TagVariant::DISPLATE);

            $tag = $newSearchTag;
            $this->entityManager->persist($tag);
        }

        // create the new image
        $image = new DisplateImage();
        $image->setSearchTag($tag);
        $tag->addDisplateImage($image);
        $image->setName($this->image->getName());
        $image->setUrl($this->image->getUrl());
        $image->setViewed(null);

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        $this->frameConfigurationService->setMode(DisplayMode::DISPLATE);
        $this->frameConfigurationService->setNextImageId($image->getId());
        $this->frameConfigurationService->setWaitForModeSwitch(true);

        return $this->redirectToRoute('app_configuration_landing');
    }
}
