<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 *
 * @extends Voter<non-empty-string, Article>
 */
final class ArticleVoter extends Voter
{
    // Defining these constants is overkill for this simple application, but for real
    // applications, it's a recommended practice to avoid relying on "magic strings"
    public const DELETE = 'delete';
    public const EDIT = 'edit';
    public const SHOW = 'show';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // this voter is only executed on Article objects and for three specific permissions
        return $subject instanceof Article && \in_array($attribute, [self::SHOW, self::EDIT, self::DELETE], true);
    }

    /**
     * @param Article $article
     */
    protected function voteOnAttribute(string $attribute, $article, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // the user must be logged in; if not, deny permission
        if (!$user instanceof User) {
            return false;
        }

        // the logic of this voter is pretty simple: if the logged-in user is the
        // author of the given blog post, grant permission; otherwise, deny it.
        // (the supports() method guarantees that $post is a Post object)
        return $user === $article->getAuthor() || in_array(User::ROLE_EDITOR, $user->getRoles(), true);
    }
}
