<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Tag;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:add-article',
    description: 'Creates articles and stores them in the database'
)]
final class AddTemplateArticleCommand extends Command
{
    protected static $defaultDescription = 'Add a new article from the command line';

    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly UserRepository $users,
        private readonly ArticleRepository $articles
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        ->setDescription(self::$defaultDescription);
        ;
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        // Ask for Article details
        $titleQuestion = new Question('Enter the article title: ');
        $title = $helper->ask($input, $output, $titleQuestion);

        $slugQuestion = new Question('Enter the article slug: ');
        $slug = $helper->ask($input, $output, $slugQuestion);

        $summaryQuestion = new Question('Enter the article summary: ');
        $summary = $helper->ask($input, $output, $summaryQuestion);

        $contentQuestion = new Question('Enter the article content: ');
        $content = $helper->ask($input, $output, $contentQuestion);

        $authorIdQuestion = new Question('Enter the author ID: ');
        $authorId = $helper->ask($input, $output, $authorIdQuestion);

        $tagQuestion = new Question('Enter tag names (e.g.: tag1, tag2): ');
        $tagsString = $helper->ask($input, $output, $tagQuestion);

        // Fetch the User entity for the author
        $author = $this->entityManager->getRepository(User::class)->find($authorId);

        if (!$author) {
            $io->error('Author not found. Please enter a valid author ID.');
            return Command::FAILURE;
        }
        if (!in_array(User::ROLE_EDITOR, $author->getRoles())) {
            $io->error('Author should be editor. Please enter a valid author ID.');
            return Command::FAILURE;
        }

        // Create and set up the Article entity
        $article = new Article();
        $article->setTitle($title);
        $article->setSlug($slug);
        $article->setSummary($summary);
        $article->setContent($content);
        $article->setAuthor($author);
        $article->setPublishedAt(new \DateTimeImmutable()); // Set to current date-time

        if (!empty($tagsString)) {
            $tagsArray = explode(',', $tagsString);
            foreach ($tagsArray as $tagItem) {
                trim($tagItem);
                $tag = $this->entityManager->getRepository(Tag::class)
                    ->findOneBy(['name' => $tagItem]);
                if (!$tag) {
                    $tag = new Tag($tagItem);  // Assuming Tag constructor requires a name.
                    $this->entityManager->persist($tag);
                }
                $article->addTag($tag);
            }
        }

        // Validate the entity
        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getMessage());
            }
            return Command::FAILURE;
        }

        // Persist the article
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $io->success('Article added successfully!');

        return Command::SUCCESS;
    }
   
}
