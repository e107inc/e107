<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp70\Tokenizer;

use PhpParser\Node;
use PhpParser\Token;
use Rector\ValueObject\Application\File;

final class WrappedInParenthesesAnalyzer
{
    public function isParenthesized(File $file, Node $node): bool
    {
        $oldTokens = $file->getOldTokens();
        $startTokenPos = $node->getStartTokenPos();
        $endTokenPos = $node->getEndTokenPos();
        $previousTokenPos = $startTokenPos >= 0 ? $startTokenPos - 1 : -1;
        $nextTokenPos = $endTokenPos >= 0 ? $endTokenPos + 1 : -1;

        return $this->isParenthesisToken($oldTokens[$previousTokenPos] ?? null, '(')
            && $this->isParenthesisToken($oldTokens[$nextTokenPos] ?? null, ')');
    }

    /**
     * Rector 2.x exposes the old tokens as {@see \PhpParser\Token} objects (which
     * extend PHP 8's PhpToken), not raw strings. A single-character token such as
     * `(` or `)` keeps its character code in ->id and the literal in ->text, so we
     * match on ->text. Pre-8.0 emulated tokens are arrays/strings, so fall back to
     * a plain string comparison for portability.
     *
     * @param mixed $token
     */
    private function isParenthesisToken($token, string $parenthesis): bool
    {
        if ($token instanceof Token) {
            return $token->text === $parenthesis;
        }

        return $token === $parenthesis;
    }
}
