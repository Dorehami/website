<?php

namespace App\Command;

use App\Repository\PostRepository;
use App\Service\UtilityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:normalize-urls',
    description: 'Normalize URLs for all existing posts',
)]
class NormalizeUrlsCommand extends Command
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly UtilityService $urlNormalizer,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $posts = $this->postRepository->findAll();
        $count = 0;

        foreach ($posts as $post) {
            if (!$post->getNormalizedUrl() && $post->getUrl()) {
                $normalizedUrl = $this->urlNormalizer->normalizeUrl($post->getUrl());
                $post->setNormalizedUrl($normalizedUrl);
                $count++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('Normalized URLs for %d posts.', $count));

        return Command::SUCCESS;
    }
}
