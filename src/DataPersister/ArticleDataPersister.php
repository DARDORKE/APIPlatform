<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

class ArticleDataPersister implements DataPersisterInterface
{

    private EntityManagerInterface $entityManager;

    private ArticleRepository $articleRepository;

    public function __construct(EntityManagerInterface $entityManager, ArticleRepository $articleRepository)
    {
        $this->entityManager = $entityManager;
        $this->articleRepository = $articleRepository;
    }

    public function supports($data): bool
    {
        return $data instanceof Article;
    }

    public function persist($data)
    {
        $article = $data;

        $slug = $this->generateSlug($article);

        $article->setSlug($slug);

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    public function remove($data)
    {
       $this->entityManager->remove($data);
       $this->entityManager->flush();
    }

    private function generateSlug(Article $article): string
    {
        $slug = preg_replace(pattern: '/[^a-z0-9]+/i', replacement: '-', subject: trim(strtolower($article->getTitle())));

        $countSlugs = $this->articleRepository->findBySameSlug($slug);
        if ($countSlugs > 0) {
            $slug = $slug . '-' . $countSlugs;
        }
        return $slug;
    }
}