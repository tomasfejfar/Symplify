<?php declare(strict_types=1);

namespace Symplify\Statie\SimilarPosts;

use Symplify\Statie\Configuration\Configuration;
use Symplify\Statie\Renderable\File\PostFile;

final class SimilarPostsResolver
{
    /**
     * @var string
     */
    public const RELATED_POSTS = 'related_posts';

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return PostFile[]
     */
    public function resolveForPostWithLimit(PostFile $mainPost): array
    {
        return $this->resolveRelatedPosts($mainPost->getRelatedPostIds());
    }

    /**
     * @param int[] $postIds
     * @return PostFile[]
     */
    private function resolveRelatedPosts(array $postIds): array
    {
        $relatedPosts = [];

        foreach ($this->getPosts() as $post) {
            if (in_array($post->getId(), $postIds, true)) {
                $relatedPosts[] = $post;
            }
        }

        return $relatedPosts;
    }

    /**
     * @return PostFile[]
     */
    private function getPosts(): array
    {
        return $this->configuration->getOptions()['posts'];
    }
}
